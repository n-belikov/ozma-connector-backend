<?php

namespace App\Services\Connectors\Abstracts;

use App\Domain\Connectors\Order;
use App\Domain\DTO\Connectors\LineItemData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface BaseConnectorInterface
 * @package App\Services\Connectors\Abstracts
 */
interface BaseConnectorInterface
{
    /**
     * получение информации по заказам
     *
     * @param int $page
     * @param int $perPage
     * @return Order[]|LengthAwarePaginator
     */
    public function getOrders(int $page, int $perPage = 10): LengthAwarePaginator;

    /**
     * Внесение трек номера
     *
     * @param int $orderId
     * @param string $trackNumber
     * @param LineItemData[] $items
     * @return bool
     */
    public function pushTrackNumber(
        int    $orderId,
        string $trackNumber,
        array  $items
    ): bool;
}