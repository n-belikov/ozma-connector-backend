<?php

namespace App\Domain\Ozma;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OzmaTable
 * @package App\Domain\Ozma
 */
class OzmaOrderBoard extends OzmaEntity implements Arrayable
{
    public ?int $stage;
    public ?int $id;
    public ?string $order_name;
    public ?string $budget;
    public ?string $start_date;
    public ?string $address_text;

    public function toArray()
    {
        return [
            "stage" => $this->stage,
            "id" => $this->id,
            "order_name" => $this->order_name,
            "budget" => $this->budget,
            "start_date" => $this->start_date,
            "address_text" => $this->address_text,
        ];
    }
}