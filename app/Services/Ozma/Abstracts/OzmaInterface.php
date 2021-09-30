<?php

namespace App\Services\Ozma\Abstracts;

use App\Domain\Ozma\Order;
use Illuminate\Support\Collection;

/**
 * Interface OzmaInterface
 * @package App\Services\Ozma\Abstracts
 */
interface OzmaInterface
{
    /**
     * @param Order[]|Collection $orders
     */
    public function sync(Collection $orders): void;

    public function syncOrderTrackNumber(): void;
}