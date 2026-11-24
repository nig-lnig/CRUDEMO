<?php

namespace App\Providers;

use App\Support\Demo\QueryRecorder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Une instance par requête HTTP
        $this->app->scoped(QueryRecorder::class);
    }

    public function boot(): void
    {
        // Les paramètres {id} des routes sont toujours numériques
        Route::pattern('id', '[0-9]+');

        // Instrumentation SQL de la présentation (local / tests uniquement)
        if ($this->app->environment('local', 'testing')) {
            DB::listen(function (QueryExecuted $query) {
                $this->app->make(QueryRecorder::class)->record($query);
            });
        }
    }
}
