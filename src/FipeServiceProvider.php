<?php

namespace  Marcuscarvalho6\Fipe;

use Illuminate\Support\ServiceProvider;

class FipeServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function register()
    {
    }
}