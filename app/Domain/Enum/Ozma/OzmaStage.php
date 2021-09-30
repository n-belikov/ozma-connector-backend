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

    public static function notPaid(): self
    {
        return new OzmaStage(self::NOT_PAID);
    }
}