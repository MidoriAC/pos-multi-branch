<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Helpers\SucursalHelper;
use App\Models\InventarioSucursal;
use App\Observers\InventarioSucursalObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
   public function boot(): void
    {
        // Compartir sucursal actual con todas las vistas
        InventarioSucursal::observe(InventarioSucursalObserver::class);
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with([
                    'sucursalActual' => SucursalHelper::getSucursalActual(),
                    'sucursalesDisponibles' => SucursalHelper::getSucursalesDisponibles(),
                    'puedeHacerCambios' => SucursalHelper::puedeHacerCambios()
                ]);
            }
        });
    }
}
