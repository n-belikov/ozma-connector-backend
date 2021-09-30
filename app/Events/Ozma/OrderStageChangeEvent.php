<?php

namespace App\Events\Ozma;

use App\Domain\Ozma\OzmaOrderBoard;
use App\Models\Ozma\Order;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStageChangeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private Order $order;
    private OzmaOrderBoard $orderBoard;

    /**
     * @param Order $order
     * @param OzmaOrderBoard $orderBoard
     */
    public function __construct(Order $order, OzmaOrderBoard $orderBoard)
    {
        $this->order = $order;
        $this->orderBoard = $orderBoard;
    }

    /**
     * @return string|null
     */
    public function getSocket(): ?string
    {
        return $this->socket;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
