<?php

namespace App\Providers;

use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Livewire::setScriptRoute(function ( $handle ) {
            return Route::get('/livewire/live-wire-js', $handle);
        });
    }
}
