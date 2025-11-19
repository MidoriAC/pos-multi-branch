<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'sucursal_id',
        'cliente_id',
        'user_id',
        'fecha_hora',
        'numero_cotizacion',
        'subtotal',
        'impuesto',
        'total',
        'observaciones',
        'validez_dias',
        'fecha_vencimiento',
        'estado',
        'venta_id',
        'fecha_conversion'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'fecha_vencimiento' => 'date',
        'fecha_conversion' => 'datetime',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2',
        'validez_dias' => 'integer'
    ];

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'cotizacion_producto')
            ->withPivot('cantidad', 'precio_unitario', 'descuento', 'subtotal')
            ->withTimestamps();
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('estado', 'PENDIENTE');
    }

    public function scopeConvertidas($query)
    {
        return $query->where('estado', 'CONVERTIDA');
    }

    public function scopeVencidas($query)
    {
        return $query->where('estado', 'VENCIDA');
    }

    public function scopeCanceladas($query)
    {
        return $query->where('estado', 'CANCELADA');
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

    public function scopeVigentes($query)
    {
        return $query->where('estado', 'PENDIENTE')
                    ->where('fecha_vencimiento', '>=', now());
    }

    // Métodos auxiliares
    public function estaVencida()
    {
        return $this->fecha_vencimiento && $this->fecha_vencimiento->isPast();
    }

    public function puedeConvertirse()
    {
        return $this->estado === 'PENDIENTE' && !$this->estaVencida();
    }

    public function puedeEditarse()
    {
        return in_array($this->estado, ['PENDIENTE']) && !$this->estaVencida();
    }

    public function puedeCancelarse()
    {
        return in_array($this->estado, ['PENDIENTE', 'VENCIDA']) && !$this->venta_id;
    }

    public function obtenerSubtotal()
    {
        return $this->subtotal;
    }

    public function obtenerDescuentoTotal()
    {
        return $this->productos->sum('pivot.descuento');
    }

    public function obtenerCantidadProductos()
    {
        return $this->productos->sum('pivot.cantidad');
    }

    public function diasRestantes()
    {
        if ($this->estaVencida()) {
            return 0;
        }
        return now()->diffInDays($this->fecha_vencimiento, false);
    }

    public function obtenerEstadoTexto()
    {
        $estados = [
            'PENDIENTE' => 'Pendiente',
            'CONVERTIDA' => 'Convertida a Venta',
            'VENCIDA' => 'Vencida',
            'CANCELADA' => 'Cancelada'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    public function obtenerEstadoBadge()
    {
        $badges = [
            'PENDIENTE' => 'warning',
            'CONVERTIDA' => 'success',
            'VENCIDA' => 'danger',
            'CANCELADA' => 'secondary'
        ];

        $clase = $badges[$this->estado] ?? 'secondary';
        $texto = $this->obtenerEstadoTexto();

        return '<span class="badge bg-' . $clase . '">' . $texto . '</span>';
    }

    // Accessors
    public function getNumeroCompletoAttribute()
    {
        return $this->numero_cotizacion;
    }

    public function getEstadoColorAttribute()
    {
        $colores = [
            'PENDIENTE' => '#ffc107',
            'CONVERTIDA' => '#28a745',
            'VENCIDA' => '#dc3545',
            'CANCELADA' => '#6c757d'
        ];

        return $colores[$this->estado] ?? '#6c757d';
    }

    // Métodos de cálculo
    public static function generarNumero(Sucursal $sucursal)
    {
        $ultimaCotizacion = self::where('sucursal_id', $sucursal->id)
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaCotizacion && preg_match('/COT-(\d+)/', $ultimaCotizacion->numero_cotizacion, $matches)) {
            $ultimoNumero = intval($matches[1]);
        } else {
            $ultimoNumero = 0;
        }

        $nuevoNumero = $ultimoNumero + 1;
        return 'COT-' . str_pad($nuevoNumero, 8, '0', STR_PAD_LEFT);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        // Al crear, verificar vencimiento
        static::creating(function ($cotizacion) {
            if (!$cotizacion->numero_cotizacion) {
                $cotizacion->numero_cotizacion = self::generarNumero($cotizacion->sucursal);
            }
        });

        // Al actualizar, verificar si está vencida
        static::updating(function ($cotizacion) {
            if ($cotizacion->estado === 'PENDIENTE' && $cotizacion->estaVencida()) {
                $cotizacion->estado = 'VENCIDA';
            }
        });
    }
}
