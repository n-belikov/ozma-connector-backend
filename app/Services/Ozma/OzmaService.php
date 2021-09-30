<?php

namespace App\Services\Ozma;

use App\Domain\Connectors\Order;
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
     * @param Collection<Order> $orders
     */
    public function sync(Collection $orders): void
    {
        $this->orderRepository->all();

//        $this->pushNewOrder($orders->first());
    }

    private function pushNewOrder(Order $order): int
    {
        $object = $this->mapper->mapTo($order, OzmaOrder::class);
        assert($object instanceof OzmaOrder);

        $object->setStage(OzmaStage::notPaid());
        $transactionItem = new TransactionEntity(TransactionEntity::INSERT, "data", OzmaTable::ORDERS);
        $transactionItem->setEntries($object->toArray());

        $orderId = $this->change(
            (new Transaction())->push($transactionItem)
        )->results[0]->id;

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

        $result = $this->change(
            $transaction
        );

        dd($object, $result);
    }

    public function syncOrderTrackNumber(): void
    {
        // TODO: Implement syncOrderTrackNumber() method.
    }

    private function request(string $method, string $url, array $params = []): ResponseInterface
    {
        $method = strtolower($method);
        $token = $this->getAccessToken();

        try {
            $result = $this->client->request($method, $url, [
                RequestOptions::HEADERS => [
                    "Authorization" => "Bearer {$token}",
                    "Content-Type" => "application/json"
                ],
                $method !== "get" ? RequestOptions::JSON : RequestOptions::QUERY => $params
            ]);
        } catch (ClientException $exception) {
            dd(json_decode($exception->getResponse()->getBody()->getContents()));
        }

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