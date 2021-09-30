<?php

namespace App\Services\Mapper\Mappers;

use App\Domain\Connectors\OrderItem;
use App\Domain\Ozma\OzmaGoods;
use App\Services\Mapper\Abstracts\MapperInterface;

/**
 * Class OrderItemToOzmaGoodsMapper
 * @package App\Services\Mapper\Mappers
 */
class OrderItemToOzmaGoodsMapper implements MapperInterface
{
    /**
     * @param OrderItem $object
     */
    public function map($object)
    {
        $item = new OzmaGoods();
        $item->name = $object->getTitle();
        $item->price = $object->getIntTotalCost() / 100;
        $item->total = ($object->getIntTotalCost() * $object->getQuantity()) / 100;
        $item->remaining = 0;
        $item->amount = $object->getQuantity();
        return $item;
    }
}