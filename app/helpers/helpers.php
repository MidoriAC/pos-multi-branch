<?php

// ============================================
// app/Helpers/helpers.php
// ============================================

use App\Models\Sucursal;

if (!function_exists('sucursal_actual')) {
    /**
     * Obtener la sucursal activa actual
     *
     * @return \App\Models\Sucursal|null
     */
    function sucursal_actual()
    {
        $sucursalId = session('sucursal_id');
        return Sucursal::find($sucursalId);
    }
}

if (!function_exists('sucursal_id')) {
    /**
     * Obtener el ID de la sucursal activa
     *
     * @return int|null
     */
    function sucursal_id()
    {
        return session('sucursal_id');
    }
}

if (!function_exists('sucursal_nombre')) {
    /**
     * Obtener el nombre de la sucursal activa
     *
     * @return string|null
     */
    function sucursal_nombre()
    {
        return session('sucursal_nombre');
    }
}

if (!function_exists('sucursal_codigo')) {
    /**
     * Obtener el código de la sucursal activa
     *
     * @return string|null
     */
    function sucursal_codigo()
    {
        return session('sucursal_codigo');
    }
}

if (!function_exists('es_superadmin')) {
    /**
     * Verificar si el usuario actual es superadmin
     *
     * @return bool
     */
    function es_superadmin()
    {
        return auth()->check() && auth()->user()->hasRole('administrador');
    }
}

if (!function_exists('puede_cambiar_sucursal')) {
    /**
     * Verificar si el usuario puede cambiar de sucursal
     *
     * @return bool
     */
    function puede_cambiar_sucursal()
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        return $user->hasRole('administrador') || $user->sucursales()->count() > 1;
    }
}
