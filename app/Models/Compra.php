<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'fecha_hora',
        'impuesto',
        'numero_comprobante',
        'total',
        'estado',
        'comprobante_id',
        'proveedore_id',
        'user_id'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function proveedore()
    {
        return $this->belongsTo(Proveedore::class);
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'compra_producto')
            ->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'precio_venta');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Accessor para obtener el subtotal
    public function getSubtotalAttribute()
    {
        return $this->total - $this->impuesto;
    }


      public function user()
    {
        return $this->belongsTo(User::class);
    }


    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    public function scopeDeSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    public function scopeDelMes($query, $mes = null, $anio = null)
    {
        $mes = $mes ?? now()->month;
        $anio = $anio ?? now()->year;
        return $query->whereMonth('fecha_hora', $mes)
                    ->whereYear('fecha_hora', $anio);
    }

    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_hora', [$fechaInicio, $fechaFin]);
    }

    // Métodos auxiliares
    public function obtenerSubtotal()
    {
        return $this->total - $this->impuesto;
    }

    public function obtenerCantidadProductos()
    {
        return $this->productos->sum('pivot.cantidad');
    }
}
