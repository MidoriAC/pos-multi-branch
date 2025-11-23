<?php

use App\Http\Controllers\categoriaController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\compraController;
use App\Http\Controllers\homeController;
use App\Http\Controllers\loginController;
use App\Http\Controllers\logoutController;
use App\Http\Controllers\marcaController;
use App\Http\Controllers\presentacioneController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\profileController;
use App\Http\Controllers\proveedorController;
use App\Http\Controllers\roleController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\userController;
use App\Http\Controllers\ventaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SucursalSwitchController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\ProductoDanadoController;
use App\Http\Controllers\InventarioSucursalController;
use App\Http\Controllers\AlertaStockController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\ReportesController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/',[homeController::class,'index'])->name('panel');

Route::middleware(['auth', 'sucursal.check'])->group(function () {
    Route::post('cambiar-sucursal', [SucursalSwitchController::class, 'cambiar'])
        ->name('sucursal.cambiar');
});

// Rutas AJAX
    Route::get('productos-danados/ubicaciones/{sucursalId}', [ProductoDanadoController::class, 'getUbicaciones'])
        ->name('productos-danados.ubicaciones');
    Route::get('productos-danados/get-stock', [ProductoDanadoController::class, 'getStock'])
        ->name('productos-danados.get-stock');

Route::resources([
    'categorias' => categoriaController::class,
    'presentaciones' => presentacioneController::class,
    'marcas' => marcaController::class,
    'productos' => ProductoController::class,
    'clientes' => clienteController::class,
    'proveedores' => proveedorController::class,
    'compras' => compraController::class,
    'ventas' => ventaController::class,
    'users' => userController::class,
    'roles' => roleController::class,
    'profile' => profileController::class,
    // 'sucursales' => SucursalController::class,
    'ubicaciones' => UbicacionController::class,
    'unidades-medida' => UnidadMedidaController::class,
    'productos-danados' => ProductoDanadoController::class,
    'alertas-stock' => AlertaStockController::class,
    'cotizaciones' => CotizacionController::class,

    // 'inventario-sucursal' => InventarioSucursalController::class,
]);




Route::resource('sucursales', SucursalController::class)
    ->parameters(['sucursales' => 'sucursal']);

Route::get('/login',[loginController::class,'index'])->name('login');
Route::post('/login',[loginController::class,'login']);
Route::get('/logout',[logoutController::class,'logout'])->name('logout');

  Route::get('sucursales/{id}/reactivar', [SucursalController::class, 'reactivar'])
        ->name('sucursales.reactivar');

    Route::get('sucursales-inactivas', [SucursalController::class, 'inactivas'])
        ->name('sucursales.inactivas');

         Route::get('users/{id}/reactivar', [userController::class, 'reactivar'])
        ->name('users.reactivar')
        ->middleware('can:editar-user');

    Route::get('users-inactivos', [userController::class, 'inactivos'])
        ->name('users.inactivos')
        ->middleware('can:ver-user');

            Route::get('productos/{id}/codigo-barras', [ProductoController::class, 'generarCodigoBarras'])
        ->name('productos.codigo-barras');
    Route::get('productos-codigos-barras/masivo', [ProductoController::class, 'generarCodigosBarrasMasivo'])
        ->name('productos.codigos-barras-masivo');

          // Rutas adicionales para aprobación
    Route::post('productos-danados/{id}/aprobar', [ProductoDanadoController::class, 'aprobar'])
        ->name('productos-danados.aprobar');
    Route::post('productos-danados/{id}/rechazar', [ProductoDanadoController::class, 'rechazar'])
        ->name('productos-danados.rechazar');




      // Rutas de inventario por sucursal (sin resource, solo las que necesitamos)
    Route::get('inventario-sucursal', [InventarioSucursalController::class, 'index'])
        ->name('inventario-sucursal.index');

    // Transferencia entre sucursales (DEBE IR ANTES de las rutas con parámetros)
    Route::get('inventario-sucursal/transferir', [InventarioSucursalController::class, 'transferir'])
        ->name('inventario-sucursal.transferir');
    Route::post('inventario-sucursal/transferir', [InventarioSucursalController::class, 'storeTransferencia'])
        ->name('inventario-sucursal.store-transferencia');

    // Rutas AJAX (también antes de las rutas con parámetros)
    Route::get('inventario-sucursal/get-inventario', [InventarioSucursalController::class, 'getInventario'])
        ->name('inventario-sucursal.get-inventario');
    Route::get('inventario-sucursal/productos-sucursal/{sucursalId}', [InventarioSucursalController::class, 'getProductosSucursal'])
        ->name('inventario-sucursal.productos-sucursal');

    // Ajuste de inventario
    Route::get('inventario-sucursal/{id}/ajustar', [InventarioSucursalController::class, 'ajustar'])
        ->name('inventario-sucursal.ajustar');
    Route::post('inventario-sucursal/{id}/ajustar', [InventarioSucursalController::class, 'storeAjuste'])
        ->name('inventario-sucursal.store-ajuste');

    // Historial de movimientos
    Route::get('inventario-sucursal/{id}/historial', [InventarioSucursalController::class, 'historial'])
        ->name('inventario-sucursal.historial');

    // Configuración de inventario
    Route::put('inventario-sucursal/{id}/config', [InventarioSucursalController::class, 'updateConfig'])
        ->name('inventario-sucursal.update-config');


         //Ventas
        Route::get('/{venta}/pdf', [VentaController::class, 'generarPDF'])->name('ventas.pdf');
        Route::get('/{venta}/xml', [VentaController::class, 'descargarXML'])->name('ventas.xml');
        Route::get('/{venta}/ticket', [VentaController::class, 'generarTicket'])->name('ventas.ticket');

        //Busqueda
          Route::post('/ventas/buscar-producto',  [VentaController::class,  'buscarProducto'])->name('ventas.buscar-producto');
    Route::post('/ventas/obtener-por-codigo',  [VentaController::class, 'obtenerPorCodigo'])->name('ventas.obtener-por-codigo');

    // Anulación
        Route::get('/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
        Route::post('/{venta}/anular', [VentaController::class, 'storeAnulacion'])->name('store-anulacion');

        // AJAX - Obtener stock de producto en sucursal
        Route::post('/get-stock-sucursal', [VentaController::class, 'getStockSucursal'])->name('get-stock-sucursal');

        // Soft delete
        Route::delete('/{venta}', [VentaController::class, 'destroy'])->name('destroy');


        //Cotizaciones
           // Generar PDF
        // Route::get('/{cotizacione}/pdf', [CotizacionController::class, 'generarPDF'])->name('cotizaciones.pdf');

        Route::prefix('cotizaciones')->group(function () {
    Route::get('{cotizacione}/pdf', [CotizacionController::class, 'generarPDF'])
        ->name('cotizaciones.pdf');
});




        // Cancelación (en lugar de eliminación)
        Route::get('/{cotizacione}/cancelar', [CotizacionController::class, 'cancelar'])->name('cotizaciones.cancelar');
        Route::post('/{cotizacione}/cancelar', [CotizacionController::class, 'storeCancelacion'])->name('cotizaciones.cancelacion.store');

        // Convertir a venta
        Route::get('/{cotizacione}/convertir', [CotizacionController::class, 'convertirAVenta'])->name('cotizaciones.convertir');

        // Duplicar cotización
        Route::get('/{cotizacione}/duplicar', [CotizacionController::class, 'duplicar'])->name('cotizaciones.duplicar');



        //Reportes
          Route::prefix('reportes')->name('reportes.')->group(function () {
        // Menú principal
        Route::get('/', [ReportesController::class, 'index'])->name('index');

        // Reporte de Ventas
        Route::get('/ventas', [ReportesController::class, 'ventas'])->name('ventas');
        Route::get('/ventas/pdf', [ReportesController::class, 'ventasPDF'])->name('ventas.pdf');

        // Reporte de Compras
        Route::get('/compras', [ReportesController::class, 'compras'])->name('compras');
        Route::get('/compras/pdf', [ReportesController::class, 'comprasPDF'])->name('compras.pdf');

        // Reporte de Inventario
        Route::get('/inventario', [ReportesController::class, 'inventario'])->name('inventario');
        Route::get('/inventario/pdf', [ReportesController::class, 'inventarioPDF'])->name('inventario.pdf');

        // Reporte de Productos Más Vendidos
        Route::get('/productos-vendidos', [ReportesController::class, 'productosVendidos'])->name('productos-vendidos');

        // Reporte de Clientes
        Route::get('/clientes', [ReportesController::class, 'clientes'])->name('clientes');
        });
       // Rutas para Alertas de Stock
Route::middleware(['auth'])->group(function () {
    Route::get('/alertas-stock', [AlertaStockController::class, 'index'])
        ->name('alertas-stock.index')
        ->middleware('can:ver-alerta-stock');

    Route::post('/alertas-stock/{id}/marcar-leida', [AlertaStockController::class, 'marcarLeida'])
        ->name('alertas-stock.marcar-leida')
        ->middleware('can:gestionar-alertas');

    Route::post('/alertas-stock/marcar-todas-leidas', [AlertaStockController::class, 'marcarTodasLeidas'])
        ->name('alertas-stock.marcar-todas-leidas')
        ->middleware('can:gestionar-alertas');

    Route::post('/alertas-stock/limpiar', [AlertaStockController::class, 'limpiarAlertas'])
        ->name('alertas-stock.limpiar')
        ->middleware('can:gestionar-alertas');

    // API para obtener alertas en tiempo real
    Route::get('/alertas-stock/api/no-leidas', [AlertaStockController::class, 'getAlertasNoLeidas'])
        ->name('alertas-stock.api.no-leidas');

         // Ruta AJAX para ubicaciones compras
    Route::get('compras/ubicaciones/{sucursalId}', [CompraController::class, 'getUbicaciones'])
        ->name('compras.ubicaciones');


   // Anulación
        Route::get('/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
        Route::post('/{venta}/anular', [VentaController::class, 'storeAnulacion'])->name('ventas.anulacion.store');
});


Route::get('/401', function () {
    return view('pages.401');
});
Route::get('/404', function () {
    return view('pages.404');
});
Route::get('/500', function () {
    return view('pages.500');
});
Route::get('/403', function () {
    return view('auth.login');
});
