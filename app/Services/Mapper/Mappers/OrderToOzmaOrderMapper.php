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
//        $subTotal = $object->getItems()->sum(
//            fn(OrderItem $item) => $item->getQuantity() * $item->getIntTotalCost()
//        );
        $subTotal = $object->getOrderPriceSummary()->getPriceSubtotal();
        $total = $object->getOrderPriceSummary()->getTotal();
        $tax = $object->getOrderPriceSummary()->getTax();
        $deliveryCost = $object->getOrderPriceSummary()->getDeliveryCost();
        $discount = $object->getOrderPriceSummary()->getDiscount();

        $object = (object)[
            "order_text" => "test",
            "source" => $object->getSource()->ozmaSource(),
            "source_number" => $object->getId(),
            "buyer" => $object->getCustomer(),
            "email" => $object->getCustomerEmail(),
            "phone" => $object->getCustomerPhone(),
            "sale_date" => $object->getDateOfSale()->toDateTimeString(),
            "payment_date" => $object->getDateOfPay() ? $object->getDateOfPay()->toDateTimeString() : null,
            "delivery_status" => $object->getStatus(),
            "address_text" => "address_text",
            "country" => $object->getAddress()->getCountry(),
            "region" => $object->getAddress()->getRegion(),
            "city" => $object->getAddress()->getCity(),
            "street" => $object->getAddress()->getStreet(),
            "delivery_address" => $object->getAddress()->getDeliveryAddress(),
            "postcode" => $object->getAddress()->getMailIndex(),
            "phone_second" => $object->getAddress()->getPhone(),
            "empty_text" => "empty_text",
            "subtotal" => $subTotal / 100,
            "empty_text_2" => "empty_text_2",
            "total_text" => "total_text",
            "delivery_cost" => $deliveryCost / 100,
            "tax" => $tax / 100,
            "discount" => $discount / 100,
            "total_second" => $total / 100,
            "responsible" => null,
        ];
        return new OzmaOrder($object);
    }
}