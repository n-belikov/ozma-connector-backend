<?php

namespace App\Domain\Enum\Connectors;

use App\Domain\Enum\BaseEnum;

/**
 * Class ConnectorType
 * @package App\Domain\Enum\Connectors
 */
class ConnectorType extends BaseEnum
{
    const EBAY = "ebay";

    /**
     * @return BaseEnum
     */
    public static function ebay(): self
    {
        return new self(self::EBAY);
    }

    public function ozmaSource(): ?string
    {
        switch ($this->value()) {
            case self::EBAY:
                return "eBay";
            default:
                return null;
        }
    }
}