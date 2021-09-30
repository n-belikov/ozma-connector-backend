<?php

namespace App\Domain\Ozma;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OzmaGoods
 * @package App\Domain\Ozma
 */
class OzmaGoods extends OzmaEntity implements Arrayable
{
    public int $order;
    public string $name;
    public float $amount;
    public float $remaining;
    public float $price;
    public float $total;

    public function toArray(): array
    {
        return [
            "order" => $this->order,
            "name" => $this->name,
            "amount" => $this->amount,
            "remaining" => $this->remaining,
            "price" => $this->price,
            "total" => $this->total,
        ];
    }
}