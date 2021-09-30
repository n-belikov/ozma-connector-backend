<?php

namespace App\Services\Mapper;

use App\Exceptions\Mapper\MapperNotFoundException;
use App\Services\Mapper\Abstracts\Mapper;
use App\Services\Mapper\Abstracts\MapperInterface;
use Illuminate\Support\Collection;

/**
 * Class MapperService
 * @package App\Services\Mapper
 */
class MapperService implements Mapper
{
    private array $mappers;

    /**
     * @param array $mappers
     */
    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * @param $from
     * @param string $to
     */
    public function mapTo($from, string $to)
    {
        if ($from instanceof Collection) {
            return $from->map(
                fn($item) => $this->mapTo($item, $to)
            );
        }
        if (is_array($from)) {
            return array_map(
                fn($item) => $this->mapTo($item, $to),
                $from
            );
        }
        foreach ($this->mappers as $mapper) {
            if ($mapper[0] === get_class($from) && $mapper[1] === $to) {
                /** @var MapperInterface $mapper */
                $mapper = $mapper[2];
                return $mapper->map($from);
            }
        }

        throw new MapperNotFoundException(get_class($from) . " => {$to}");
    }
}