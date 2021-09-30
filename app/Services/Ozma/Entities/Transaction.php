<?php

namespace App\Services\Ozma\Entities;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * Class Transaction
 * @package App\Services\Ozma\Entities
 */
class Transaction implements Arrayable
{
    private Collection $operations;

    public function __construct()
    {
        $this->flush();
    }

    public function push(TransactionEntity $entity): Transaction
    {
        $this->operations->push($entity);
        return $this;
    }

    public function flush(): Transaction
    {
        $this->operations = new Collection();
        return $this;
    }

    public function toArray(): array
    {
        $array = [
            "operations" => $this->operations->toArray()
        ];
        $this->flush();

        return $array;
    }
}