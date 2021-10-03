<?php

namespace App\Providers;

use App\Domain\Enum\Connectors\ConnectorType;
use App\Repositories\Ozma\Abstracts\OrderRepository;
use App\Services\Connectors\Abstracts\ConnectorAggregator;
use App\Services\Connectors\Abstracts\SyncInterface;
use App\Services\Connectors\ConnectorAggregatorService;
use App\Services\Connectors\EbayConnector;
use App\Services\Connectors\SiteConnector;
use App\Services\Connectors\SyncService;
use App\Services\Mapper\Abstracts\Mapper;
use App\Services\Ozma\Abstracts\OzmaInterface;
use App\Services\Ozma\OzmaService;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\ServiceProvider;

/**
 * Class ConnectorServiceProvider
 * @package App\Providers
 */
class ConnectorServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(OzmaInterface::class,
            fn() => new OzmaService(
                $this->app[OrderRepository::class],
                $this->app[Mapper::class],
                $this->app[CacheRepository::class],
                config("ozma.url"),
                config("ozma.auth_url"),
                config("ozma.client_id"),
                config("ozma.client_secret"),
                config("ozma.username"),
                config("ozma.password")
            )
        );

        $this->app->bind(ConnectorAggregator::class, function () {
            $connectors = [
                ConnectorType::EBAY => new EbayConnector(
                    $this->app[CacheRepository::class],
                    config("services.ebay.base_url"),
                    config("services.ebay.client_id"),
                    config("services.ebay.client_secret"),
                    config("services.ebay.refresh_token")
                ),
                ConnectorType::SITE => new SiteConnector(
                    $this->app[CacheRepository::class],
                    config("services.site.url"),
                    config("services.site.secret_id"),
                    config("services.site.secret_key"),
                    config("services.site.version")
                )
            ];

            return new ConnectorAggregatorService(
                $connectors
            );
        });

        $this->app->bind(SyncInterface::class, SyncService::class);
    }
}