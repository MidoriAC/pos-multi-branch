<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionFel extends Model
{
    use HasFactory;

    protected $table = 'configuracion_fel';

    protected $fillable = [
        'sucursal_id',
        'nit_emisor',
        'nombre_comercial',
        'nombre_emisor',
        'codigo_establecimiento',
        'afiliacion_iva',
        'usuario_certificador',
        'clave_certificador',
        'url_certificador',
        'llave_certificacion',
        'ambiente',
        'proveedor_fel',
        'estado'
    ];

    protected $casts = [
        'estado' => 'boolean'
    ];

    protected $hidden = [
        'clave_certificador',
        'llave_certificacion'
    ];

    // Relaciones
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }
}
