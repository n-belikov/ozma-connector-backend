<?php

namespace App\Domain\Ozma;

use App\Domain\Enum\Ozma\OzmaStage;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OzmaOrder
 * @package App\Domain\Connectors
 */
class OzmaOrder extends OzmaEntity implements Arrayable
{
    public string $order_text;
    public string $source;
    public ?string $source_number;
    public int $id;
    public ?string $buyer;
    public ?string $email;
    public ?string $phone;
    public ?string $sale_date;
    public ?string $payment_date;
    public ?string $delivery_status;
    public ?string $address_text;
    public ?string $country;
    public ?string $region;
    public ?string $city;
    public ?string $street;
    public ?string $delivery_address;
    public ?string $postcode;
    public ?string $phone_second;
    public ?string $empty_text;
    public ?float $subtotal;
    public ?string $empty_text_2;
    public ?string $total_text;
    public ?float $delivery_cost;
    public ?float $tax;
    public ?float $discount;
    public ?float $total_second;
    public ?int $stage;
    public ?string $responsible;

    public function setStage(OzmaStage $stage): self
    {
        $this->stage = $stage->value();
        return $this;
    }

    public function toArray(): array
    {
        $vars = array_keys(get_class_vars(static::class));
        $result = [];
        foreach ($vars as $var) {
            $result[$var] = $this->{$var} ?? null;
        }
        return $result;
    }
}