<?php

namespace App\Domain\Connectors;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OrderPriceSummary
 * @package App\Domain\Connectors
 */
class OrderPriceSummary implements Arrayable
{
    private int $priceSubtotal;
    private int $deliveryCost;
    private int $tax;
    private int $total;

    public function __construct(
        string $priceSubtotal,
        string $deliveryCost,
        string $tax,
        string $total
    )
    {
        $this->priceSubtotal = intval(floatval($priceSubtotal) * 100);
        $this->deliveryCost = intval(floatval($deliveryCost) * 100);
        $this->tax = intval(floatval($tax) * 100);
        $this->total = intval(floatval($total) * 100);
    }

    /**
     * @return int
     */
    public function getPriceSubtotal(): int
    {
        return $this->priceSubtotal;
    }

    /**
     * @return int
     */
    public function getDeliveryCost(): int
    {
        return $this->deliveryCost;
    }

    /**
     * @return int
     */
    public function getTax(): int
    {
        return $this->tax;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    public function toArray(): array
    {
        return [
            "price_subtotal" => $this->priceSubtotal,
            "delivery_cost" => $this->deliveryCost,
            "tax" => $this->tax,
            "total" => $this->total,
        ];
    }
}