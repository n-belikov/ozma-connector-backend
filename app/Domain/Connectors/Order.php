<?php

namespace App\Domain\Connectors;

use App\Domain\Enum\Connectors\ConnectorType;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Class Order
 * @package App\Domain\Connectors
 */
class Order implements Arrayable
{
    private ConnectorType $source;
    private string $id;
    private string $customer;
    private string $customerEmail;
    private string $customerPhone;
    private Carbon $dateOfSale;
    private ?Carbon $dateOfPay;
    private ?string $deliveryType;
    private ?string $trackNumber;
    private OrderStatus $status;
    private ?OrderAddress $address;

    /** @var Collection<OrderItem> */
    private Collection $items;
    private OrderPriceSummary $orderPriceSummary;

    public function __construct(
        ConnectorType     $source,
        string            $id,
        string            $customer,
        string            $customerEmail,
        string            $customerPhone,
        Carbon            $dateOfSale,
        ?Carbon           $dateOfPay,
        ?string           $deliveryType,
        ?string           $trackNumber,
        OrderStatus       $status,
        ?OrderAddress     $address = null,
        OrderPriceSummary $orderPriceSummary
    )
    {
        $this->source = $source;
        $this->id = $id;
        $this->customer = $customer;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->dateOfSale = $dateOfSale;
        $this->dateOfPay = $dateOfPay;
        $this->deliveryType = $deliveryType;
        $this->trackNumber = $trackNumber;
        $this->status = $status;
        $this->address = $address;
        $this->orderPriceSummary = $orderPriceSummary;

        $this->items = new Collection();
    }

    /**
     * @return ConnectorType
     */
    public function getSource(): ConnectorType
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getCustomer(): string
    {
        return $this->customer;
    }

    /**
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * @return string
     */
    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    /**
     * @return Carbon
     */
    public function getDateOfSale(): Carbon
    {
        return $this->dateOfSale;
    }

    /**
     * @return Carbon|null
     */
    public function getDateOfPay(): ?Carbon
    {
        return $this->dateOfPay;
    }

    /**
     * @return string|null
     */
    public function getDeliveryType(): ?string
    {
        return $this->deliveryType;
    }

    /**
     * @return string|null
     */
    public function getTrackNumber(): ?string
    {
        return $this->trackNumber;
    }

    /**
     * @return OrderStatus
     */
    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @return OrderAddress|null
     */
    public function getAddress(): ?OrderAddress
    {
        return $this->address;
    }

    public function toArray(): array
    {
        return [
            "source" => $this->source->value(),
            "id" => $this->id,
            "customer" => $this->customer,
            "customerEmail" => $this->customerEmail,
            "customerPhone" => $this->customerPhone,
            "dateOfSale" => $this->dateOfSale ? $this->dateOfSale->toDateTimeString() : null,
            "dateOfPay" => $this->dateOfPay ? $this->dateOfPay->toDateTimeString() : null,
            "deliveryType" => $this->deliveryType,
            "trackNumber" => $this->trackNumber,
            "status" => $this->status->value(),
            "address" => $this->address->toArray(),
            "items" => $this->items->toArray(),
            "orderPriceSummary" => $this->orderPriceSummary->toArray()
        ];
    }

    /**
     * @return OrderPriceSummary
     */
    public function getOrderPriceSummary(): OrderPriceSummary
    {
        return $this->orderPriceSummary;
    }

    /**
     * @return Collection
     */
    public function getItems(): Collection
    {
        return $this->items;
    }
}