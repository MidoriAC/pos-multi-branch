<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoDanado extends Model
{
    use HasFactory;

    protected $table = 'productos_danados';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'ubicacion_id',
        'cantidad',
        'motivo',
        'descripcion',
        'costo_perdida',
        'fecha_registro',
        'user_id',
        'aprobado_por',
        'estado'
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'costo_perdida' => 'decimal:2',
        'cantidad' => 'integer'
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

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function aprobador()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }
}
