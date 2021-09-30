<?php

namespace App\Console\Commands\Ozma;

use App\Services\Ozma\Abstracts\OzmaInterface;
use Illuminate\Console\Command;

class SyncStages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozma:sync-stages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(OzmaInterface $service)
    {
        $this->service = $service;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->service->syncStages();
        return 0;
    }
}
