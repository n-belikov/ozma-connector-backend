<?php

namespace App\Domain\Connectors;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OrderItem
 * @package App\Domain\Connectors
 */
class OrderItem implements Arrayable
{
    private string $lineItemId;
    private string $title;
    private string $totalCost;
    private int $quantity;

    /**
     * @param string $lineItemId
     * @param string $title
     * @param string $totalCost
     * @param int $quantity
     */
    public function __construct(
        string $lineItemId,
        string $title,
        string $totalCost,
        int    $quantity
    )
    {
        $this->lineItemId = $lineItemId;
        $this->title = $title;
        $this->totalCost = $totalCost;
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getLineItemId(): string
    {
        return $this->lineItemId;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTotalCost(): string
    {
        return $this->totalCost;
    }

    /**
     * Вернёт число помноженное на сто.
     * Пример: 10.1 * 100 = 10100
     * @return int
     */
    public function getIntTotalCost(): int
    {
        return floatval($this->totalCost) * 100;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            "line_item_id" => $this->lineItemId,
            "title" => $this->title,
            "total_cost" => $this->totalCost,
            "int_total_cost" => $this->getIntTotalCost(),
            "quantity" => $this->quantity,
        ];
    }
}