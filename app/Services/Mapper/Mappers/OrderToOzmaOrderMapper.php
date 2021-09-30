<?php

namespace App\Services\Mapper\Mappers;

use App\Domain\Connectors\OrderItem;
use App\Domain\Ozma\Order;
use App\Domain\Ozma\OzmaOrder;
use App\Services\Mapper\Abstracts\MapperInterface;

/**
 * Class OrderToOzmaOrderMapper
 * @package App\Services\Mapper\Mappers
 */
class OrderToOzmaOrderMapper implements MapperInterface
{
    /**
     * @param Order $object
     * @return OzmaOrder
     */
    public function map($object)
    {
        $subTotal = $object->getItems()->sum(
            fn(OrderItem $item) => $item->getQuantity() * $item->getIntTotalCost()
        );

        $object = (object)[
            "order_text" => "test",
            "source" => $object->getSource()->ozmaSource(),
            "source_number" => $object->getId(),
            "buyer" => $object->getCustomer(),
            "email" => $object->getCustomerEmail(),
            "phone" => $object->getCustomerPhone(),
            "sale_date" => $object->getDateOfSale()->toDateTimeString(),
            "payment_date" => $object->getDateOfPay()->toDateTimeString(),
            "delivery_status" => $object->getStatus(),
            "address_text" => "address_text",
            "country" => $object->getAddress()->getCountry(),
            "region" => $object->getAddress()->getRegion(),
            "city" => $object->getAddress()->getRegion(),
            "street" => $object->getAddress()->getStreet(),
            "delivery_address" => $object->getAddress()->getDeliveryAddress(),
            "postcode" => $object->getAddress()->getMailIndex(),
            "phone_second" => $object->getAddress()->getPhone(),
            "empty_text" => "empty_text",
            "subtotal" => $subTotal / 100,
            "empty_text_2" => "empty_text_2",
            "total_text" => "total_text",
            "delivery_cost" => 11,
            "tax" => 9,
            "total_second" => $subTotal / 100,
            "responsible" => null,
        ];
        return new OzmaOrder($object);
    }
}