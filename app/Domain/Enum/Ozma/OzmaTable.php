<?php

namespace App\Domain\Enum\Ozma;

use App\Domain\Enum\BaseEnum;

/**
 * Class OzmaTable
 * @package App\Domain\Enum\Ozma
 */
class OzmaTable extends BaseEnum
{
     // Для получения
    const ORDER_FORM = "order_form"; // Для получения конкретного заказа
    const ORDERS_BOARD = "orders_board"; // Для получения дашборда
    const GOODS_TABLE = "goods_table";
    const GOODS_DISPATCH = "goods_dispatch_table";

    // Для записи
    const ORDERS = "orders";
    const GOODS = "goods";
}