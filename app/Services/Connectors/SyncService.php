<?php

namespace App\Services\Connectors;

use App\Domain\Enum\Connectors\ConnectorType;
use App\Services\Connectors\Abstracts\ConnectorAggregator;
use App\Services\Connectors\Abstracts\SyncInterface;
use App\Services\Ozma\Abstracts\OzmaInterface;
use Illuminate\Console\OutputStyle;

/**
 * Class SyncService
 * @package App\Services\Connectors
 */
class SyncService implements SyncInterface
{
    /** @var ConnectorAggregator */
    private ConnectorAggregator $aggregator;

    /** @var OzmaInterface */
    private OzmaInterface $ozmaService;

    /**
     * @param ConnectorAggregator $aggregator
     * @param OzmaInterface $ozmaService
     */
    public function __construct(
        ConnectorAggregator $aggregator,
        OzmaInterface       $ozmaService
    )
    {
        $this->aggregator = $aggregator;
        $this->ozmaService = $ozmaService;
    }

    /**
     * @inheritDoc
     */
    public function syncIn(OutputStyle $style): void
    {
        $connectors = [
            ConnectorType::EBAY => [
                "type" => ConnectorType::ebay(),
                "per_page" => 30,
            ],
        ];
        foreach (ConnectorType::all() as $type) {
            if (!isset($connectors[$type->value()])) {
                $connectors[$type->value()] = [
                    "type" => $type,
                    "per_page" => 50
                ];
            }
        }

        $orders = collect();
        $style->writeln("<info>Load info from connectors...</info>");

        foreach ($connectors as $connector) {
            $type = $connector["type"];
            $perPage = $connector["per_page"];
            $pageNumber = 1;
            $count = 0;
            do {
                $style->writeln("<info>Connector: {$type}, page: {$pageNumber}</info>");
                $items = $this->aggregator->getOrdersFrom($type, $pageNumber, $perPage);
                $orders = $orders->merge($items->items());
                $count += $items->count();
                $pageNumber++;
            } while ($items->lastPage() >= $pageNumber);
            $style->writeln("<info>Count: {$count}</info>");
            unset($items);
        }
        $style->writeln("<info>Total: {$orders->count()}</info>");

        $style->writeln("<info>Sync in ozma</info>");
        $this->ozmaService->sync($orders);
        $style->writeln("<info>Done!</info>");
    }
}