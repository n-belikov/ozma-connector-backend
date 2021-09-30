<?php

namespace App\Services\Connectors\Abstracts;

use App\Domain\Enum\Connectors\ConnectorType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface ConnectorAggregator
 * @package App\Services\Connectors\Abstracts
 */
interface ConnectorAggregator
{
    /**
     * @param ConnectorType $type
     * @param int $page
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOrdersFrom(ConnectorType $type, int $page, int $perPage = 10): LengthAwarePaginator;
}