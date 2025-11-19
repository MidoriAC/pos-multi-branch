<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursales';

    protected $fillable = [
        'nombre',
        'codigo',
        'direccion',
        'telefono',
        'email',
        'nit_establecimiento',
        'codigo_establecimiento',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // ==================== RELACIONES ====================

    /**
     * Relación con usuarios (muchos a muchos)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_sucursal')
            ->withPivot('es_principal')
            ->withTimestamps();
    }

    /**
     * Relación con ubicaciones (una sucursal tiene muchas ubicaciones)
     */
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class);
    }

    /**
     * Relación con inventario (una sucursal tiene muchos inventarios)
     */
    public function inventarios()
    {
        return $this->hasMany(InventarioSucursal::class);
    }

    /**
     * Relación con ventas (una sucursal tiene muchas ventas)
     */
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    /**
     * Relación con compras (una sucursal tiene muchas compras)
     */
    public function compras()
    {
        return $this->hasMany(Compra::class);
    }

    /**
     * Relación con cotizaciones (una sucursal tiene muchas cotizaciones)
     */
    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    /**
     * Relación con productos dañados
     */
    public function productosDanados()
    {
        return $this->hasMany(ProductoDanado::class);
    }

    /**
     * Relación con configuración FEL
     */
    public function configuracionFel()
    {
        return $this->hasOne(ConfiguracionFel::class);
    }

    /**
     * Relación con series FEL
     */
    public function seriesFel()
    {
        return $this->hasMany(SerieFel::class);
    }

    /**
     * Relación con alertas de stock
     */
    public function alertasStock()
    {
        return $this->hasMany(AlertaStock::class);
    }

    /**
     * Relación con pedidos de compra
     */
    public function pedidosCompra()
    {
        return $this->hasMany(PedidoCompra::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope para obtener solo sucursales activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para obtener solo sucursales inactivas
     */
    public function scopeInactivas($query)
    {
        return $query->where('estado', 0);
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Verificar si la sucursal está activa
     */
    public function estaActiva()
    {
        return $this->estado == 1;
    }

    /**
     * Obtener el nombre completo con código
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->codigo} - {$this->nombre}";
    }

    /**
     * Verificar si tiene configuración FEL activa
     */
    public function tieneFelActivo()
    {
        return $this->configuracionFel && $this->configuracionFel->estado == 1;
    }

    /**
     * Obtener total de stock en la sucursal
     */
    public function getTotalStockAttribute()
    {
        return $this->inventarios()->sum('stock_actual');
    }

    /**
     * Obtener cantidad de productos diferentes
     */
    public function getCantidadProductosAttribute()
    {
        return $this->inventarios()->count();
    }

    /**
     * Verificar si tiene movimientos (ventas o compras)
     */
    public function tieneMovimientos()
    {
        return $this->ventas()->count() > 0 || $this->compras()->count() > 0;
    }

    /**
     * Obtener total de ventas del mes actual
     */
    public function ventasMesActual()
    {
        return $this->ventas()
            ->whereMonth('fecha_hora', now()->month)
            ->whereYear('fecha_hora', now()->year)
            ->where('estado', 1)
            ->sum('total');
    }

    /**
     * Obtener total de compras del mes actual
     */
    public function comprasMesActual()
    {
        return $this->compras()
            ->whereMonth('fecha_hora', now()->month)
            ->whereYear('fecha_hora', now()->year)
            ->where('estado', 1)
            ->sum('total');
    }
}
