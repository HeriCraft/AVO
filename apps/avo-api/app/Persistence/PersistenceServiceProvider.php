<?php

namespace App\Persistence;

use Illuminate\Support\ServiceProvider;

class PersistenceServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Bindings de persistance si nécessaire
    }

    public function boot()
    {
        // Enregistrement explicite des migrations ou factories si besoin
    }
}
