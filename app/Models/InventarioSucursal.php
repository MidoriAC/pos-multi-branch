<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventarioSucursal extends Model
{
    use HasFactory;

    protected $table = 'inventario_sucursal';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'ubicacion_id',
        'stock_actual',
        'stock_minimo',
        'stock_maximo',
        'precio_venta',
        'estado'
    ];

    protected $casts = [
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer',
        'stock_maximo' => 'integer',
        'precio_venta' => 'decimal:2',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }

    public function alertas()
{
    return $this->hasMany(AlertaStock::class, 'producto_id', 'producto_id')
        ->where('sucursal_id', $this->sucursal_id);
}

public function alertaActiva()
{
    return $this->hasOne(AlertaStock::class, 'producto_id', 'producto_id')
        ->where('sucursal_id', $this->sucursal_id)
        ->where('leida', false)
        ->latest('fecha_alerta');
}

// Método helper para verificar si tiene alerta activa
public function tieneAlertaActiva()
{
    return $this->alertaActiva()->exists();
}

// Método helper para obtener el tipo de alerta
public function getTipoAlerta()
{
    if ($this->stock_actual == 0) {
        return 'STOCK_AGOTADO';
    }

    if ($this->stock_actual <= $this->stock_minimo) {
        return 'STOCK_MINIMO';
    }

    if ($this->stock_actual <= ($this->stock_minimo * 1.1)) {
        return 'PROXIMO_VENCER';
    }

    return null;
}
}
