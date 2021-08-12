<?php

namespace AgenterLab\Setting;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Arr;

class Provider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/setting.php'  => $this->app->getConfigurationPath('setting'),
        ], 'setting');

        $this->override();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('setting.manager', function ($app) {
            return new Manager($app);
        });

        $this->app->singleton('setting', function ($app) {
            return $app['setting.manager']->driver();
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/setting.php', 'setting');
    }

    private function override()
    {
        $override = config('setting.override', []);

        foreach (Arr::dot($override) as $config_key => $setting_key) {
            $config_key = is_string($config_key) ? $config_key : $setting_key;

            try {
                if (! is_null($value = setting($setting_key))) {
                    config([$config_key => $value]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}
