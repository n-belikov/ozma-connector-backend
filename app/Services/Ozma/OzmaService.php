<?php

namespace App\Services\Ozma;

use App\Domain\Connectors\Order as OrderConnector;
use App\Domain\Connectors\OrderStatus;
use App\Domain\Ozma\OzmaOrderBoard;
use App\Events\Ozma\OrderStageChangeEvent;
use App\Exceptions\Ozma\SyncErrorException;
use App\Models\Ozma\Order;
use App\Domain\Enum\Ozma\OzmaStage;
use App\Domain\Ozma\OzmaGoods;
use App\Domain\Ozma\OzmaOrder;
use App\Domain\Enum\Ozma\OzmaTable;
use App\Repositories\Ozma\Abstracts\OrderRepository;
use App\Services\Mapper\Abstracts\Mapper;
use App\Services\Ozma\Abstracts\OzmaInterface;
use App\Services\Ozma\Entities\Transaction;
use App\Services\Ozma\Entities\TransactionEntity;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Class OzmaService
 * @package App\Services\Ozma
 */
class OzmaService implements OzmaInterface
{
    private string $cacheKey = "ozma.access_token";

    private string $url;

    private Client $client;

    private string $authUrl;
    private string $clientId;
    private string $clientSecret;
    private string $username;
    private string $password;

    private CacheRepository $cacheRepository;

    private Mapper $mapper;

    /**
     * @param OrderRepository $orderRepository
     * @param Mapper $mapper
     * @param CacheRepository $cacheRepository
     * @param string $url
     * @param string $authUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     */
    public function __construct(
        OrderRepository $orderRepository,
        Mapper          $mapper,
        CacheRepository $cacheRepository,
        string          $url,
        string          $authUrl,
        string          $clientId,
        string          $clientSecret,
        string          $username,
        string          $password
    )
    {
        $this->orderRepository = $orderRepository;
        $this->mapper = $mapper;
        $this->cacheRepository = $cacheRepository;

        $this->url = $url;
        $this->authUrl = $authUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->username = $username;
        $this->password = $password;
        $this->client = new Client([
            "base_uri" => $url,
        ]);
    }

    /**
     * @param Collection<OrderConnector> $orders
     */
    public function sync(Collection $orders): void
    {

        $orders = $orders->filter(fn(OrderConnector $item) => $item->getStatus() !== OrderStatus::notStarted());
        $orders = $orders->filter(fn (OrderConnector $item) => $item->getOrderPriceSummary()->getDiscount() > 0);
        $orders = $orders->slice(0, 10);


        /** @var Order[]|Collection<Order> $databaseOrders */
        $databaseOrders = $this->orderRepository->all();

        /** @var OrderConnector $order */
        foreach ($orders as $order) {
            /** @var Order|null $item */
            $item = $databaseOrders->where("connector_id", $order->getId())->where("connector_type", $order->getSource()->value())->first();
            if ($item) {
                $this->updateOrder(
                    $order,
                    $item->ozma_id
                );
            } else {
                $this->pushNewOrder(
                    $order
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function syncStages(): void
    {
        /** @var Order[]|Collection<Order> $orders */
        $orders = $this->orderRepository->all()->keyBy("ozma_id");
        $result = $this->request("get", "/views/by_name/data/" . OzmaTable::ORDERS_BOARD . "/entries");

        $json = json_decode($result->getBody()->getContents());

        $json = OzmaOrderBoard::collection($json)
            ->whereIn("id", $orders->pluck("ozma_id"))
            ->values();

        foreach ($json as $item) {
            $order = $orders[$item->id];
            if ($item->stage !== $order->ozma_stage_id) {
                event(
                    new OrderStageChangeEvent($order, $item)
                );
                $orders[$item->id] = $this->orderRepository->update([
                    "ozma_stage_id" => $item->stage,
                ], $order->id);
            }
        }
    }

    /**
     * @param OrderConnector $order
     * @return Order
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function pushNewOrder(OrderConnector $order): Order
    {
        $object = $this->mapper->mapTo($order, OzmaOrder::class);
        assert($object instanceof OzmaOrder);

        $object->setStage($stage = OzmaStage::notPaid());
        $transactionItem = new TransactionEntity(TransactionEntity::INSERT, "data", OzmaTable::ORDERS);
        $transactionItem->setEntries($object->toArray());

        try {
            $orderId = $this->change(
                (new Transaction())->push($transactionItem)
            )->results[0]->id;
        } catch (ClientException $exception) {
            throw SyncErrorException::fromResponse("Error sync order", $exception->getResponse(), $transactionItem->toArray());
        }

        /** @var OzmaGoods[] $object */
        $object = $this->mapper->mapTo($order->getItems(), OzmaGoods::class)->map(function (OzmaGoods $goods) use ($orderId) {
            $goods->order = $orderId;
            return $goods;
        });

        $transaction = new Transaction();
        foreach ($object as $item) {
            $transactionItem = new TransactionEntity(TransactionEntity::INSERT, "data", OzmaTable::GOODS);
            $transactionItem->setEntries($item->toArray());
            $transaction->push($transactionItem);
        }

        try {
            $this->change(
                $transaction
            );
        } catch (ClientException $exception) {
            throw SyncErrorException::fromResponse("Sync goods error", $exception->getResponse(), $transaction->toArray());
        }

        return $this->orderRepository->create([
            "connector_id" => $order->getId(),
            "connector_type" => $order->getSource()->value(),
            "ozma_id" => $orderId,
            "ozma_stage_id" => $stage
        ]);
    }

    /**
     * @param OrderConnector $order
     * @return Order
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function updateOrder(OrderConnector $order, int $orderId): void
    {
        $object = $this->mapper->mapTo($order, OzmaOrder::class);
        assert($object instanceof OzmaOrder);

        $array = $object->toArray();
        if (!($object->stage ?? false)) {
            unset($array["stage"]);
        }
        $transactionItem = new TransactionEntity(TransactionEntity::UPDATE, "data", OzmaTable::ORDERS);
        $transactionItem->setId($orderId)->setEntries($array);

        try {
            $this->change(
                (new Transaction())->push($transactionItem)
            );
        } catch (ClientException $exception) {
            throw new SyncErrorException("Error: " . json_encode(json_decode($exception->getResponse()->getBody()->getContents()), JSON_PRETTY_PRINT) . "\n" . json_encode($transactionItem->toArray(), JSON_PRETTY_PRINT));
        }

        // TODO: fix
//        /** @var OzmaGoods[] $object */
//        $object = $this->mapper->mapTo($order->getItems(), OzmaGoods::class)->map(function (OzmaGoods $goods) use ($orderId) {
//            $goods->order = $orderId;
//            return $goods;
//        });
//
//        $transaction = new Transaction();
//        foreach ($object as $item) {
//            $transactionItem = new TransactionEntity(TransactionEntity::INSERT, "data", OzmaTable::GOODS);
//            $transactionItem->setEntries($item->toArray());
//            $transaction->push($transactionItem);
//        }
//
//        try {
//            $this->change(
//                $transaction
//            );
//        } catch (ClientException $exception) {
//            throw new SyncErrorException("Error sync goods");
//        }
    }

    public function syncOrderTrackNumber(): void
    {
        // TODO: Implement syncOrderTrackNumber() method.
    }

    private function request(string $method, string $url, array $params = []): ResponseInterface
    {
        $method = strtolower($method);
        $token = $this->getAccessToken();

//        try {
        $result = $this->client->request($method, $url, [
            RequestOptions::HEADERS => [
                "Authorization" => "Bearer {$token}",
                "Content-Type" => "application/json"
            ],
            $method !== "get" ? RequestOptions::JSON : RequestOptions::QUERY => $params
        ]);
//        } catch (ClientException $exception) {
//            dd("[{$method}] {$url}", json_decode($exception->getResponse()->getBody()->getContents()));
//        }

        return $result;
    }

    /**
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getAccessToken(): string
    {
        if ($this->cacheRepository->has($this->cacheKey)) {
            return $this->cacheRepository->get($this->cacheKey);
        }

        $response = (new Client())->post($this->authUrl, [
            RequestOptions::FORM_PARAMS => [
                "client_id" => $this->clientId,
                "client_secret" => $this->clientSecret,
                "username" => $this->username,
                "password" => $this->password,
                "grant_type" => "password",
            ]
        ])->getBody()->getContents();

        $response = json_decode($response);

        $token = $response->access_token;

        $this->cacheRepository->put($this->cacheKey, $token, Carbon::now()->addSeconds($response->expires_in));
        return $token;
    }

    /**
     * @param string $table
     * @param array $parameters
     * @param string $scheme
     * @return \stdClass
     */
    public function getView(string $table, array $parameters = [], string $scheme = "data"): \stdClass
    {
        $result = $this->request("get", "/views/by_name/{$scheme}/{$table}/entries", $parameters);

        return json_decode($result->getBody()->getContents());
    }

    /**
     * @param Transaction $transaction
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function change(Transaction $transaction): \stdClass
    {
        $response = $this->request(
            "post",
            "/transaction",
            $transaction->toArray()
        );
        return json_decode($response->getBody()->getContents());
    }
}