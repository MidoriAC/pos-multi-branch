<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogFel extends Model
{
    use HasFactory;

    protected $table = 'log_fel';

    protected $fillable = [
        'venta_id',
        'tipo_documento',
        'serie',
        'numero',
        'uuid',
        'respuesta_certificador',
        'estado',
        'fecha_certificacion',
        'intentos'
    ];

    protected $casts = [
        'fecha_certificacion' => 'datetime',
        'intentos' => 'integer'
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
