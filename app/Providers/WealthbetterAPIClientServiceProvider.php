<?php
/**
 * Created by PhpStorm.
 * User: Daimon
 * Date: 2015/6/16
 * Time: 14:42
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Extensions\WealthbetterAPIClient as WealthbetterAPIClient;

class WealthbetterAPIClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['ServiceProvider']->resolver(function ($clientConfig) {
            return new WealthbetterAPIClient($clientConfig);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}