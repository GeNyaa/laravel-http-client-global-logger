<?php

namespace Onlime\LaravelHttpClientGlobalLogger\Providers;

use Illuminate\Http\Client\PendingRequest;
use Monolog\Handler\StreamHandler;
use Onlime\LaravelHttpClientGlobalLogger\Mixins\PendingRequestMixin;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    /**
     * This method is used by spatie/laravel-package-tools to setup the package.
     *
     * More info: https://github.com/spatie/laravel-package-tools
     *
     * @param Package $package
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-http-client-global-logger')
            ->hasConfigFile();
    }

    /**
     * Will be called at the end of the register method by spatie/laravel-package-tools.
     *
     * More info: https://github.com/spatie/laravel-package-tools
     */
    public function packageRegistered()
    {
        if (config('http-client-global-logger.enabled') &&
            !config('http-client-global-logger.mixin')) {
            $this->app->register(EventServiceProvider::class);
        }
    }

    /**
     * Will be called at the end of the boot method by spatie/laravel-package-tools.
     *
     * More info: https://github.com/spatie/laravel-package-tools
     */
    public function packageBooted()
    {
        $channel = config('http-client-global-logger.channel');
        if (!array_key_exists($channel, config('logging.channels'))) {
            // Define new logging channel
            // see https://stackoverflow.com/a/59791539/5982842
            $this->app->make('config')->set("logging.channels.$channel", [
                'driver' => 'monolog',
                'level' => 'debug',
                'handler' => StreamHandler::class,
                'with' => [
                    'stream' => config('http-client-global-logger.logfile'),
                ],
            ]);
        }

        // Mixin variant of using Http:log($name) instead of global logging
        if (config('http-client-global-logger.mixin')) {
            PendingRequest::mixin(new PendingRequestMixin());
        }
    }
}
