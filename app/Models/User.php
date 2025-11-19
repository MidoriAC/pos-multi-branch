<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
         'estado',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
         'estado' => 'boolean',
    ];

    public function ventas(){
        return $this->hasMany(Venta::class);
    }

    public function sucursales()
{
    return $this->belongsToMany(Sucursal::class, 'user_sucursal')
        ->withPivot('es_principal')
        ->withTimestamps();
}

public function sucursalPrincipal()
{
    return $this->sucursales()->wherePivot('es_principal', 1)->first();
}


public function compras()
{
    return $this->hasMany(Compra::class, 'user_id');
}

public function cotizaciones()
{
    return $this->hasMany(Cotizacion::class);
}

public function productosDanados()
{
    return $this->hasMany(ProductoDanado::class, 'user_id');
}
}
