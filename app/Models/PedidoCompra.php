<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PedidoCompra extends Model
{
    use HasFactory;

    protected $table = 'pedidos_compra';

    protected $fillable = [
        'numero_pedido',
        'fecha_pedido',
        'fecha_entrega_estimada',
        'fecha_entrega_real',
        'proveedore_id',
        'sucursal_id',
        'subtotal',
        'impuesto',
        'total',
        'estado',
        'observaciones',
        'user_id',
        'compra_id'
    ];

    protected $casts = [
        'fecha_pedido' => 'datetime',
        'fecha_entrega_estimada' => 'date',
        'fecha_entrega_real' => 'date',
        'subtotal' => 'decimal:2',
        'impuesto' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(Proveedore::class, 'proveedore_id');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
            ->withPivot('cantidad_solicitada', 'cantidad_recibida', 'precio_unitario', 'subtotal')
            ->withTimestamps();
    }
}
