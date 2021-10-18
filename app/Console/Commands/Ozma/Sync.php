<?php

namespace App\Console\Commands\Ozma;

use App\Services\Connectors\Abstracts\SyncInterface;
use Illuminate\Console\Command;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ozma:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Выгрузка данных из коннекторов';

    /** @var SyncInterface */
    private SyncInterface $service;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SyncInterface $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->service->syncIn(
            $this->output
        );
        return 0;
    }
}
