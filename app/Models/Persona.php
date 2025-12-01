<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;

    public function documento(){
        return $this->belongsTo(Documento::class);
    }

    public function proveedore(){
        return $this->hasOne(Proveedore::class);
    }

    public function cliente(){
        return $this->hasOne(Cliente::class);
    }

     protected $fillable = [
        'tipo_persona',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'email',
        'telefono',
        'nit',
        'documento_id',
        'numero_documento',
        'estado'
    ];
}
