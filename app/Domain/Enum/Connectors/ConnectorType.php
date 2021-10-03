<?php

namespace App\Domain\Enum\Connectors;

use App\Domain\Enum\BaseEnum;

/**
 * Class ConnectorType
 * @package App\Domain\Enum\Connectors
 */
class ConnectorType extends BaseEnum
{
    const SITE = "site";

    const EBAY = "ebay";


    /**
     * @return BaseEnum
     */
    public static function ebay(): self
    {
        return new self(self::EBAY);
    }

    /**
     * @return BaseEnum
     */
    public static function site(): self
    {
        return new self(self::SITE);
    }

    public function ozmaSource(): ?string
    {
        switch ($this->value()) {
            case self::EBAY:
                return "eBay";
            case self::SITE:
                return "Сайт";
            default:
                return $this->value();
        }
    }
}