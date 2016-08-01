<?php

namespace Lfalmeida\Lbase\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class LbaseServiceProvider
 *
 * @package Lfalmeida\Lbase\Providers
 */
class LbaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Inicializa o macro Response
         */
        require __DIR__ . implode(DIRECTORY_SEPARATOR, ['','..', 'Macros', 'apiResponse.php']);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}