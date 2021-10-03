<?php

namespace App\Domain\Connectors;

use App\Domain\Enum\BaseEnum;

/**
 * Class OrderStatus
 * @package App\Domain\Connectors
 */
class OrderStatus extends BaseEnum
{
    const NOT_STARTED = "not_started";

    const PAID = "payed";

    const IN_PROGRESS = "in_progress";

    const FULFILLED = "fulfilled";

    public static function notStarted(): OrderStatus
    {
        return new OrderStatus(self::NOT_STARTED);
    }

    public static function paid(): OrderStatus
    {
        return new OrderStatus(self::PAID);
    }

    public static function inProgress(): OrderStatus
    {
        return new OrderStatus(self::IN_PROGRESS);
    }

    public static function fulfilled(): OrderStatus
    {
        return new OrderStatus(self::FULFILLED);
    }
}