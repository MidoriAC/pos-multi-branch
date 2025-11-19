<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertaStock extends Model
{
    use HasFactory;

    protected $table = 'alertas_stock';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'tipo_alerta',
        'stock_actual',
        'stock_minimo',
        'fecha_alerta',
        'leida',
        'fecha_lectura',
        'user_id_lectura'
    ];

    protected $casts = [
        'fecha_alerta' => 'datetime',
        'fecha_lectura' => 'datetime',
        'leida' => 'boolean',
        'stock_actual' => 'integer',
        'stock_minimo' => 'integer'
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

    public function lector()
    {
        return $this->belongsTo(User::class, 'user_id_lectura');
    }
}
