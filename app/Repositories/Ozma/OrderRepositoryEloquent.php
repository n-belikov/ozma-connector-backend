<?php

namespace App\Repositories\Ozma;

use App\Models\Ozma\Order;
use App\Repositories\Abstracts\BaseRepository;
use App\Repositories\Ozma\Abstracts\OrderRepository;

/**
 * Class OrderRepositoryEloquent
 * @package App\Repositories\Ozma
 */
class OrderRepositoryEloquent extends BaseRepository implements OrderRepository
{
    /**
     * @inheritDoc
     */
    protected function model(): string
    {
        return Order::class;
    }
}