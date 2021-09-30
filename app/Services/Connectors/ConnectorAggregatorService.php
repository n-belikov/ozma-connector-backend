<?php

namespace App\Services\Connectors;

use App\Domain\Enum\Connectors\ConnectorType;
use App\Services\Connectors\Abstracts\BaseConnectorInterface;
use App\Services\Connectors\Abstracts\ConnectorAggregator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class ConnectorAggregatorService
 * @package App\Services\Connectors
 */
class ConnectorAggregatorService implements ConnectorAggregator
{
    /** @var BaseConnectorInterface[] */
    private array $connectors;

    /**
     * @param BaseConnectorInterface[] $connectors
     */
    public function __construct(
        array $connectors
    )
    {
        $this->connectors = $connectors;
    }

    /**
     * @inheritDoc
     */
    public function getOrdersFrom(ConnectorType $type, int $page, int $perPage = 10): LengthAwarePaginator
    {
        return $this->connectors[$type->value()]->getOrders($page, $perPage);
    }
}