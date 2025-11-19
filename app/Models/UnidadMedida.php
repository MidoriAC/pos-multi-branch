<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    use HasFactory;

    protected $table = 'unidades_medida';

    protected $fillable = [
        'nombre',
        'abreviatura',
        'tipo',
        'codigo_fel',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    // Relaciones
    public function productos()
    {
        return $this->hasMany(Producto::class, 'unidad_medida_id');
    }

    // Método para obtener el nombre del tipo
    public function getTipoNombreAttribute()
    {
        $tipos = [
            'peso' => 'Peso',
            'volumen' => 'Volumen',
            'longitud' => 'Longitud',
            'unidad' => 'Unidad',
            'area' => 'Área',
            'tiempo' => 'Tiempo'
        ];

        return $tipos[$this->tipo] ?? $this->tipo;
    }
}
