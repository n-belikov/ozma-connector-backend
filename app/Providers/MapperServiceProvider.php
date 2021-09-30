<?php

namespace App\Providers;

use App\Domain\Connectors\Order;
use App\Domain\Connectors\OrderItem;
use App\Domain\Ozma\OzmaGoods;
use App\Domain\Ozma\OzmaOrder;
use App\Services\Mapper\Abstracts\Mapper;
use App\Services\Mapper\Mappers\OrderItemToOzmaGoodsMapper;
use App\Services\Mapper\Mappers\OrderToOzmaOrderMapper;
use App\Services\Mapper\MapperService;
use Illuminate\Support\ServiceProvider;

/**
 * Class MapperServiceProvider
 * @package App\Providers
 */
class MapperServiceProvider extends ServiceProvider
{
    private array $mappers = [
        [Order::class, OzmaOrder::class, OrderToOzmaOrderMapper::class],
        [OrderItem::class, OzmaGoods::class, OrderItemToOzmaGoodsMapper::class],
    ];

    public function register()
    {
        $this->app->bind(Mapper::class, function () {
            return new MapperService(
                $this->mappers()
            );
        });
    }

    private function mappers(): array
    {
        $arrays = [];
        foreach ($this->mappers as $mapper) {
            $this->app->bind($mapper[2]);
            $mapper[2] = $this->app[$mapper[2]];
            $arrays[] = $mapper;
        }

        return $arrays;
    }
}