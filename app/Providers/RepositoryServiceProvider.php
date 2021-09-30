<?php

namespace App\Providers;

use App\Repositories\Ozma\Abstracts\OrderRepository;
use App\Repositories\Ozma\OrderRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

/**
 * Class RepositoryServiceProvider
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositories = [
        OrderRepository::class => OrderRepositoryEloquent::class
    ];

    public function register(): void
    {
        foreach ($this->repositories as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}