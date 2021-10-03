<?php

namespace App\Domain\Enum\Ozma;

use App\Domain\Enum\BaseEnum;

/**
 * Class OzmaStage
 * @package App\Domain\Enum\Ozma
 */
class OzmaStage extends BaseEnum
{
    const NOT_PAID = 7;

    const DISCOUNT_OFFER = 8;

    const PAYED = 1;

    const SHIPPMENT_REGISTERED = 2;

    const EXPIRED = 3;

    const SENDED_TO_CLIENT = 4;

    const ORDER_DELIVERED = 5;

    const ORDER_CANCELED = 6;

    public static function notPaid(): self
    {
        return new OzmaStage(self::NOT_PAID);
    }
}