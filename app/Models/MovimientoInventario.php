<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'ubicacion_origen_id',
        'ubicacion_destino_id',
        'compra_id',
        'venta_id',
        'cotizacion_id',
        'producto_danado_id',
        'motivo',
        'user_id',
        'fecha_movimiento'
    ];

    protected $casts = [
        'fecha_movimiento' => 'datetime',
        'cantidad' => 'integer'
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursalOrigen()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_origen_id');
    }

    public function sucursalDestino()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_destino_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
