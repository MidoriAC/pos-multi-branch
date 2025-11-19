<?php

// ============================================
// app/Models/Ubicacion.php
// ============================================

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    protected $fillable = [
        'sucursal_id',
        'codigo',
        'nombre',
        'tipo',
        'seccion',
        'capacidad_maxima',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean',
        'capacidad_maxima' => 'integer'
    ];

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioSucursal::class);
    }
}
