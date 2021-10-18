<?php

namespace App\Services\Connectors;

use App\Domain\Connectors\Order;
use App\Domain\Connectors\OrderAddress;
use App\Domain\Connectors\OrderItem;
use App\Domain\Connectors\OrderPriceSummary;
use App\Domain\Connectors\OrderStatus;
use App\Domain\Enum\Connectors\ConnectorType;
use App\Services\Connectors\Abstracts\BaseConnectorInterface;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Информация по API:
 * https://www.developer.ebay.com/api-docs/sell/fulfillment/resources/methods
 *
 * Class EbayConnector
 * @package App\Services\Connectors
 */
class EbayConnector implements BaseConnectorInterface
{
    /** @var CacheRepository */
    private CacheRepository $cacheRepository;
    private Client $client;
    private string $baseUrl;
    private string $refreshToken;
    private string $clientId;

    private string $hashKey = "ebay.access-token";

    private string $apiTokenUrl = "https://api.ebay.com/identity/v1/oauth2/token";

    private bool $apiCacheEnable = true;

    private int $apiCacheTtl = 900;

    /**
     * @param CacheRepository $cacheRepository
     * @param string $baseUrl
     * @param string $clientId
     * @param string $clientSecret
     * @param string $refreshToken
     */
    public function __construct(
        CacheRepository $cacheRepository,
        string          $baseUrl,
        string          $clientId,
        string          $clientSecret,
        string          $refreshToken,
        bool            $cacheEnable = false,
        int             $cacheTtl = 0
    )
    {
        $this->cacheRepository = $cacheRepository;

        $this->client = new Client();
        $this->baseUrl = $baseUrl;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->refreshToken = $refreshToken;

        $this->apiCacheEnable = $cacheEnable;
        $this->apiCacheTtl = $cacheTtl;
    }

    /**
     * @param int $perPage
     * @return LengthAwarePaginator
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getOrders(int $page, int $perPage = 10): LengthAwarePaginator
    {
        $orders = $this->request("order", [
            "limit" => $perPage,
            "offset" => max(0, $page - 1) * $perPage
        ]);
        $items = json_decode($orders, true);

        $orders = new Collection();

        foreach ($items["orders"] as $item) {
            $shippingTo = $item["fulfillmentStartInstructions"][0]["shippingStep"]["shipTo"] ?? null;
            $payment = $item["paymentSummary"]["payments"][0] ?? null;


            $status = $item["orderFulfillmentStatus"];
            $paymentStatus = $payment["paymentStatus"] ?? null;
            if ($paymentStatus && $status === "NOT_STARTED") {
                $status = $paymentStatus;
            }

            switch ($status) {
                default:
                case "NOT_STARTED":
                    $status = OrderStatus::notStarted();
                    break;
                case "PAID":
                    $status = OrderStatus::paid();
                    break;
                case "IN_PROGRESS":
                    $status = OrderStatus::inProgress();
                    break;
                case "FULFILLED":
                    $status = OrderStatus::fulfilled();
                    break;
                    // TODO: добавить статус canceled
            }

            $order = new Order(
                ConnectorType::ebay(),
                $item["orderId"] ?? "",
                $shippingTo["fullName"] ?? "",
                $shippingTo["email"] ?? "",
                $phone = $shippingTo["primaryPhone"]["phoneNumber"] ?? "",
                Carbon::parse($item["creationDate"]),
                $payment && isset($payment["paymentDate"]) ? Carbon::parse($payment["paymentDate"]) : null,
                null,
                null,
                $status,
                new OrderAddress(
                    $shippingTo["contactAddress"]["countryCode"] ?? "",
                    $shippingTo["contactAddress"]["stateOrProvince"] ?? "",
                    $shippingTo["contactAddress"]["city"] ?? "",
                    $shippingTo["contactAddress"]["addressLine1"] ?? "",
                    $shippingTo["fullName"],
                    $shippingTo["contactAddress"]["postalCode"] ?? "",
                    $phone
                ),
                new OrderPriceSummary(
                    $item["pricingSummary"]["priceSubtotal"]["value"] ?? "0",
                    $item["pricingSummary"]["deliveryCost"]["value"] ?? "0",
                    $item["pricingSummary"]["tax"]["value"] ?? "0",
                    $item["pricingSummary"]["total"]["value"] ?? "0"
                )
            );

            foreach ($item["lineItems"] ?? [] as $lineItem) {
                $order->getItems()->push(
                    new OrderItem(
                        $lineItem["lineItemId"],
                        $lineItem["title"],
                        $lineItem["lineItemCost"]["value"],
                        $lineItem["quantity"]
                    )
                );
            }

            $orders->push(
                $order
            );
        }


        return new \Illuminate\Pagination\LengthAwarePaginator(
            $orders,
            $items["total"],
            $perPage,
            $page
        );
    }

    /**
     * Получение access_token'а. Он действителен 7200 секунд, его можно обновить. Для этого я использую еще refresh_token.
     * После получения кладу его в кэш.
     *
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function getAccessToken(): string
    {
        if ($this->cacheRepository->has($this->hashKey)) {
            return $this->cacheRepository->get($this->hashKey);
        }

        $basicToken = base64_encode("{$this->clientId}:{$this->clientSecret}");
        $body = $this->client->post($this->apiTokenUrl, [
            "form_params" => [
                "grant_type" => "refresh_token",
                "refresh_token" => $this->refreshToken,
            ],
            "headers" => [
                "Authorization" => "Basic " . $basicToken
            ]
        ])->getBody()->getContents();

        /**
         * @var array $object = {"access_token" => "token", "expires_in" => 0}
         */
        $object = json_decode($body, true);

        $timeout = Carbon::now()->addSeconds($object["expires_in"]);
        $this->cacheRepository->put($this->hashKey, $token = $object["access_token"], $timeout);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function pushTrackNumber(int $orderId, string $trackNumber, array $items): bool
    {
        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = [
                "lineItemId" => $item->itemId,
                "quantity" => $item->quantity
            ];
        }

        return $this->pushTrackNumberInEbay(
            $orderId,
            $trackNumber,
            $lineItems
        );
    }

    /**
     * @param int $orderId
     * @param string $trackNumber
     * @param array $lineItemIds
     * @param string $shippingServiceCode
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function pushTrackNumberInEbay(
        int    $orderId,
        string $trackNumber,
        array  $lineItemIds,
        string $shippingServiceCode = "EconomyShippingFromOutsideUS"
    ): bool
    {
        $result = $this->request("order/{$orderId}/shipping_fulfillment", [
            "lineItems" => $lineItemIds,
            "shippedDate" => $this->dateToIso8601Z(Carbon::now()),
            "shippingCarrierCode" => $shippingServiceCode,
            "trackingNumber" => $trackNumber
        ]);

        if (!$result) {
            return false;
        }

        // TODO: посмотреть что выйдет результатом. По идеи должна вернуться ссылка на получение инфы.
        return true;
    }

    private function getFilteredOrders(Carbon $startModifiedAt, Carbon $endModifiedAt): ?string
    {
        $now = Carbon::now();
        $start = $this->dateToIso8601Z($startModifiedAt);
        $end = $this->dateToIso8601Z($endModifiedAt->greaterThan($now) ? $now : $endModifiedAt);

        $filter = "lastmodifieddate:[{$start}..{$end}]";

        return $this->request("order", [
            "filter" => $filter,
        ]);
    }

    /**
     * @param string $path
     * @param array $params
     * @param string $method
     * @return string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(string $path, array $params = [], string $method = "get"): ?string
    {
        if ($this->apiCacheEnable) {
            $hashKey = "ebay.api." . md5($path . json_encode($params) . $method);
            if ($this->cacheRepository->has($hashKey)) {
                return $this->cacheRepository->get($hashKey);
            }
        }

        $accessToken = $this->getAccessToken();

        $method = strtolower($method);
        try {
            $url = preg_match("/^(http|https)\:/ui", $path)
                ? $path
                : rtrim($this->baseUrl, "/") . "/" . rtrim($path, "/");

            $request = $this->client->request(
                $method,
                $url,
                [
                    $method === "get" ? RequestOptions::QUERY : RequestOptions::JSON => $params,
                    RequestOptions::HEADERS => [
                        "Authorization" => "Bearer " . $accessToken
                    ]
                ]
            )->getBody()->getContents();

            if ($this->apiCacheEnable) {
                $this->cacheRepository->put($hashKey, $request, $this->apiCacheTtl);
            }

        } catch (ClientException $exception) {
            $message = $exception->getResponse()->getBody()->getContents();
            // TODO: убрать дд, сделать нормальный Exception
            dd($message, "error", $exception->getResponse()->getStatusCode());
            return null;
        }

        return $request;
    }

    /**
     * Форматирование даты в нужный вид
     *
     * @param Carbon|null $date
     * @return string|null
     */
    private function dateToIso8601Z(?Carbon $date): ?string
    {
        return $date ? $date->format("Y-m-d\TH:i:s.v\Z") : null;
    }
}