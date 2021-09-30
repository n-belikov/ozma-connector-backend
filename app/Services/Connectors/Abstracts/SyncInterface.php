<?php

namespace App\Services\Connectors\Abstracts;

use Illuminate\Console\OutputStyle;

/**
 * Interface SyncInterface
 * @package App\Services\Connectors\Abstracts
 */
interface SyncInterface
{
    /**
     * @param OutputStyle $style
     */
    public function syncIn(OutputStyle $style): void;
}