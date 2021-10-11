<?php

namespace App\Services\Connectors;

use App\Domain\Connectors\Order;
use App\Domain\Connectors\OrderAddress;
use App\Domain\Connectors\OrderItem;
use App\Domain\Connectors\OrderPriceSummary;
use App\Domain\Connectors\OrderStatus;
use App\Domain\DTO\Connectors\LineItemData;
use App\Domain\Enum\Connectors\ConnectorType;
use App\Services\Connectors\Abstracts\BaseConnectorInterface;
use App\Services\Connectors\Site\JwtGenerator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class SiteConnector
 * @package App\Services\Connectors
 */
class SiteConnector implements BaseConnectorInterface
{
    const STATUS_NEW = "1";

    const STATUS_PAID = "2";

    private CacheRepository $cacheRepository;

    private string $url;
    private string $secretId;
    private string $secretKey;
    private string $version;

    private Client $client;

    private bool $apiCacheEnable = true;

    private int $apiCacheTtl = 900;

    /**
     * @param CacheRepository $cacheRepository
     * @param string $url
     * @param string $secretId
     * @param string $secretKey
     * @param string $version
     */
    public function __construct(
        CacheRepository $cacheRepository,
        string          $url,
        string          $secretId,
        string          $secretKey,
        string          $version,
        bool            $cacheEnable = false,
        int             $cacheTtl = 0
    )
    {
        $this->cacheRepository = $cacheRepository;
        $this->url = $url;
        $this->secretId = $secretId;
        $this->secretKey = $secretKey;
        $this->version = $version;

        $this->client = new Client();

        $this->apiCacheEnable = $cacheEnable;
        $this->apiCacheTtl = $cacheTtl;
    }

    public function getOrders(int $page, int $perPage = 10): LengthAwarePaginator
    {
        $json = $this->request([
            "page" => $page,
            "per_page" => $perPage
        ]);

        $json = json_decode($json);
        $items = collect();
        foreach ($json->items as $item) {
            $paymentDate = null;
            foreach ($item->logs as $log) {
                if ($log->entry === self::STATUS_PAID) {
                    $paymentDate = Carbon::parse($log->timestamp);
                }
            }

            if (!$item->status->id) {
                continue;
            }
            $status = $this->mapStatus($item->status->id);

            $obj = new Order(
                ConnectorType::site(),
                $item->order_id,
                $item->user->fullname,
                $item->user->email,
                $item->user->phone ?? $item->user->mobilephone,
                Carbon::parse($item->order_createdon),
                $paymentDate,
                $item->delivery->name,
                $item->address->metro ?? null, // В системе сайта - это трек номер
                $status,
                new OrderAddress(
                    $item->address->country ?? "",
                    $item->address->region ?? "",
                    $item->address->city ?? "",
                    "{$item->address->street}, {$item->address->building}",
                    $item->address->receiver ?? "",
                    $item->address->index ?? "",
                    $item->address->phone ?? ""
                ),
                new OrderPriceSummary(
                    $item->order_cart_cost,
                    $item->order_delivery_cost,
                    '0',
                    $item->order_cost,
                    $item->coupon->discount_amount
                )
            );

            foreach ($item->products as $product) {
                $obj->getItems()->push(
                    new OrderItem(
                        $product->id,
                        $product->name,
                        $product->cost,
                        intval($product->count)
                    )
                );
            }

            $items->push($obj);
        }

        return new \Illuminate\Pagination\LengthAwarePaginator($items, $json->total, $perPage, $page);
    }

    private function mapStatus(int $status): OrderStatus
    {
        switch ($status) {
            default:
            case 1:
                return OrderStatus::notStarted();
            case 2:
                return OrderStatus::paid();
            case 5:
                return OrderStatus::inProgress();
            case 3:
                return OrderStatus::fulfilled();
            case 4:
                return OrderStatus::canceled();
        }
    }

    public function pushTrackNumber(int $orderId, string $trackNumber, array $items): bool
    {
        return false;
    }

    private function request(array $params = []): ?string
    {
        if ($this->apiCacheEnable) {
            $hashKey = "site.api." . md5(json_encode($params));
            if ($this->cacheRepository->has($hashKey)) {
                return $this->cacheRepository->get($hashKey);
            }
        }


        $token = new JwtGenerator(md5($this->version . $this->secretKey));

        $request = $this->client->get($this->url, [
            RequestOptions::QUERY => $params,
            RequestOptions::HEADERS => [
                "Authorization" => $token = $token->make([
                    "id" => $this->secretId
                ])
            ]
        ])->getBody()->getContents();

        if ($this->apiCacheEnable) {
            $this->cacheRepository->put($hashKey, $request, $this->apiCacheTtl);
        }

        return $request;
    }
}