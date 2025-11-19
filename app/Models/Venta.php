<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'sucursal_id',
        'cliente_id',
        'user_id',
        'comprobante_id',
        'fecha_hora',
        'numero_comprobante',
        'serie',
        'impuesto',
        'total',
        'estado',
        'tipo_factura', // 'RECI' (Recibo) o 'FACT' (FEL)
        'numero_autorizacion_fel',
        'fecha_certificacion_fel',
        'xml_fel'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_certificacion_fel' => 'datetime',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_venta')
            ->withTimestamps()
            ->withPivot('cantidad', 'precio_venta', 'descuento');
    }

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function logFel()
    {
        return $this->hasOne(LogFel::class);
    }

    public function anulacionFel()
    {
        return $this->hasOne(AnulacionFel::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    // Métodos auxiliares

    /**
     * Verificar si es factura FEL
     */
    public function esFEL()
    {
        return $this->tipo_factura === 'FACT';
    }

    /**
     * Verificar si es recibo simple
     */
    public function esRecibo()
    {
        return $this->tipo_factura === 'RECI';
    }

    /**
     * Verificar si puede anularse
     */
    public function puedeAnularse()
    {
        // No puede anularse si:
        // 1. Ya está anulada (estado = 0)
        // 2. Ya tiene una anulación registrada
        // 3. Ha pasado el tiempo límite

        if ($this->estado == 0) {
            return false;
        }

        if ($this->anulacionFel) {
            return false;
        }

        // Verificar tiempo límite (3 días por defecto)
        $diasLimite = config('ventas.dias_limite_anulacion', 3);
        $fechaLimite = Carbon::parse($this->fecha_hora)->addDays($diasLimite);

        if (now()->gt($fechaLimite)) {
            return false;
        }

        return true;
    }

    /**
     * Verificar si está certificada (para FEL)
     */
    public function estaCertificada()
    {
        return $this->esFEL() && !empty($this->numero_autorizacion_fel);
    }

    /**
     * Verificar si está anulada
     */
    public function estaAnulada()
    {
        return $this->estado == 0 || $this->anulacionFel !== null;
    }

    /**
     * Obtener subtotal (sin IVA)
     */
    public function getSubtotalAttribute()
    {
        return $this->total - $this->impuesto;
    }

    /**
     * Obtener días desde la venta
     */
    public function getDiasDesdeVentaAttribute()
    {
        return now()->diffInDays($this->fecha_hora);
    }

    /**
     * Obtener días restantes para anular
     */
    public function getDiasRestantesAnulacionAttribute()
    {
        $diasLimite = config('ventas.dias_limite_anulacion', 3);
        $fechaLimite = Carbon::parse($this->fecha_hora)->addDays($diasLimite);

        if (now()->gt($fechaLimite)) {
            return 0;
        }

        return now()->diffInDays($fechaLimite);
    }

    /**
     * Obtener estado legible
     */
    public function getEstadoTextoAttribute()
    {
        if ($this->estaAnulada()) {
            return 'Anulada';
        }

        if ($this->esFEL()) {
            return $this->estaCertificada() ? 'Certificada' : 'Pendiente Certificación';
        }

        return 'Activa';
    }

    /**
     * Obtener color del badge según estado
     */
    public function getEstadoColorAttribute()
    {
        if ($this->estaAnulada()) {
            return 'danger';
        }

        if ($this->esFEL()) {
            return $this->estaCertificada() ? 'success' : 'warning';
        }

        return 'primary';
    }

    /**
     * Scope para ventas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('estado', 1);
    }

    /**
     * Scope para ventas de una sucursal
     */
    public function scopeDeSucursal($query, $sucursalId)
    {
        return $query->where('sucursal_id', $sucursalId);
    }

    /**
     * Scope para ventas FEL
     */
    public function scopeFEL($query)
    {
        return $query->where('tipo_factura', 'FACT');
    }

    /**
     * Scope para recibos
     */
    public function scopeRecibos($query)
    {
        return $query->where('tipo_factura', 'RECI');
    }

    /**
     * Scope para ventas de hoy
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_hora', today());
    }

    /**
     * Scope para ventas del mes
     */
    public function scopeEsteMes($query)
    {
        return $query->whereYear('fecha_hora', now()->year)
                    ->whereMonth('fecha_hora', now()->month);
    }

    /**
     * Scope para ventas certificadas
     */
    public function scopeCertificadas($query)
    {
        return $query->where('tipo_factura', 'FACT')
                    ->whereNotNull('numero_autorizacion_fel');
    }

    /**
     * Scope para ventas pendientes de certificación
     */
    public function scopePendientesCertificacion($query)
    {
        return $query->where('tipo_factura', 'FACT')
                    ->whereNull('numero_autorizacion_fel')
                    ->where('estado', 1);
    }
}
