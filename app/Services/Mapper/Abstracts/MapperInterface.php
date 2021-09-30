<?php

namespace App\Services\Mapper\Abstracts;

/**
 * Interface MapperInterface
 * @package App\Services\Mapper\Abstracts
 */
interface MapperInterface
{
    /**
     * @param $object
     */
    public function map($object);
}