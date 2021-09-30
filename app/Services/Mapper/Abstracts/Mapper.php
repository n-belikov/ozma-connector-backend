<?php

namespace App\Services\Mapper\Abstracts;

/**
 * Interface Mapper
 * @package App\Services\Mapper\Abstracts
 */
interface Mapper
{
    /**
     * @param $from
     * @param string $to
     * @return mixed
     */
    public function mapTo($from, string $to);
}