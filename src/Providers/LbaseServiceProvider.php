<?php

namespace Lfalmeida\Lbase\Providers;

use Illuminate\Support\ServiceProvider;

class LbaseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
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