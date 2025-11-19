<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Producto extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'fecha_vencimiento',
        'marca_id',
        'presentacione_id',
        'unidad_medida_id',
        'img_path',
        'estado'
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'estado' => 'boolean'
    ];

    public function compras()
    {
        return $this->belongsToMany(Compra::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_compra', 'precio_venta');
    }

    public function ventas()
    {
        return $this->belongsToMany(Venta::class)->withTimestamps()
            ->withPivot('cantidad', 'precio_venta', 'descuento');
    }

    public function categorias()
    {
        return $this->belongsToMany(Categoria::class)->withTimestamps();
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class);
    }

    public function presentacione()
    {
        return $this->belongsTo(Presentacione::class);
    }

    public function unidadMedida()
    {
        return $this->belongsTo(UnidadMedida::class, 'unidad_medida_id');
    }

    public function inventarios()
    {
        return $this->hasMany(InventarioSucursal::class);
    }

    public function inventarioEnSucursal($sucursalId)
    {
        return $this->inventarios()->where('sucursal_id', $sucursalId)->first();
    }

    public function stockEnSucursal($sucursalId)
    {
        $inventario = $this->inventarioEnSucursal($sucursalId);
        return $inventario ? $inventario->stock_actual : 0;
    }

    public function cotizaciones()
    {
        return $this->belongsToMany(Cotizacion::class, 'cotizacion_producto')
            ->withPivot('cantidad', 'precio_unitario', 'descuento', 'subtotal')
            ->withTimestamps();
    }

    public function productosDanados()
    {
        return $this->hasMany(ProductoDanado::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function handleUploadImage($image)
    {
        $file = $image;
        $name = time() . $file->getClientOriginalName();
        Storage::putFileAs('/public/productos/', $file, $name, 'public');
        return $name;
    }

    // Método para obtener el nombre completo con presentación
    public function getNombreCompletoAttribute()
    {
        $nombre = $this->nombre;
        if ($this->presentacione) {
            $nombre .= ' - ' . $this->presentacione->caracteristica->nombre;
        }
        if ($this->unidadMedida) {
            $nombre .= ' (' . $this->unidadMedida->abreviatura . ')';
        }
        return $nombre;
    }
}
