<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnulacionFel extends Model
{
    use HasFactory;

    protected $table = 'anulaciones_fel';

    protected $fillable = [
        'venta_id',
        'uuid_documento_anular',
        'uuid_anulacion',
        'motivo',
        'fecha_anulacion',
        'user_id',
        'estado'
    ];

    protected $casts = [
        'fecha_anulacion' => 'datetime'
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
