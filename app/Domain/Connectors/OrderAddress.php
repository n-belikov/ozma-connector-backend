<?php

namespace App\Domain\Connectors;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Class OrderAddress
 * @package App\Domain\Connectors
 */
class OrderAddress implements Arrayable
{
    private string $country;
    private string $region;
    private string $city;
    private string $street;
    private string $deliveryAddress;
    private string $mailIndex;
    private string $phone;

    /**
     * @param string $country
     * @param string $region
     * @param string $city
     * @param string $street
     * @param string $deliveryAddress
     * @param string $mailIndex
     * @param string $phone
     */
    public function __construct(
        string $country,
        string $region,
        string $city,
        string $street,
        string $deliveryAddress,
        string $mailIndex,
        string $phone
    )
    {
        $this->country = $country;
        $this->region = $region;
        $this->city = $city;
        $this->street = $street;
        $this->deliveryAddress = $deliveryAddress;
        $this->mailIndex = $mailIndex;
        $this->phone = $phone;
    }

    public function toArray()
    {
        return [
            "country" => $this->country,
            "region" => $this->region,
            "city" => $this->city,
            "street" => $this->street,
            "delivery_address" => $this->deliveryAddress,
            "mail_index" => $this->mailIndex,
            "phone" => $this->phone,
        ];
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getDeliveryAddress(): string
    {
        return $this->deliveryAddress;
    }

    /**
     * @return string
     */
    public function getMailIndex(): string
    {
        return $this->mailIndex;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }
}