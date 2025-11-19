<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerieFel extends Model
{
    use HasFactory;

    protected $table = 'series_fel';

    protected $fillable = [
        'sucursal_id',
        'tipo_documento',
        'serie',
        'numero_actual',
        'numero_inicial',
        'numero_final',
        'fecha_resolucion',
        'numero_resolucion',
        'estado'
    ];

    protected $casts = [
        'fecha_resolucion' => 'date',
        'numero_actual' => 'integer',
        'numero_inicial' => 'integer',
        'numero_final' => 'integer',
        'estado' => 'boolean'
    ];

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
