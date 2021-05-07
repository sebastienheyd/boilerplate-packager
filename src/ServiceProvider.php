<?php

namespace Sebastienheyd\BoilerplatePackager;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/config' => config_path('boilerplate')], ['boilerplate', 'boilerplate-packager']);

            config([
                'filesystems.disks.packages' => [
                    'driver' => 'local',
                    'root' => base_path('packages'),
                ],
            ]);

            $this->commands([
                Commands\Packager::class,
            ]);
        }
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/packager.php', 'boilerplate.packager');
    }
}
