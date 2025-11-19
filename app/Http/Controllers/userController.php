<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Sucursal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class userController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:ver-user|crear-user|editar-user|eliminar-user', ['only' => ['index']]);
        $this->middleware('permission:crear-user', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-user', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-user', ['only' => ['destroy']]);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['roles', 'sucursales'])
            // ->where('estado', 1)
            ->get();

        return view('user.index', compact('users'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('estado', 1)->get();

        return view('user.create', compact('roles', 'sucursales'));
    }

    /**
     * Store a newly created resource in storage.
     */
public function store(StoreUserRequest $request)
    {
        try {
            DB::beginTransaction();

            // Encriptar contraseña
            $fieldHash = Hash::make($request->password);
            $request->merge(['password' => $fieldHash]);

            // Crear usuario con estado activo por defecto
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $fieldHash,
                'estado' => 1
            ]);

            $user->assignRole($request->role);

            if ($request->has('sucursales') && is_array($request->sucursales)) {
                foreach ($request->sucursales as $sucursalId) {
                    $esPrincipal = ($request->sucursal_principal == $sucursalId) ? 1 : 0;

                    $user->sucursales()->attach($sucursalId, [
                        'es_principal' => $esPrincipal,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuario registrado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                ->with('error', 'Error al registrar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
  public function show(User $user)
    {
        // Cargar relaciones
        $user->load(['roles', 'sucursales', 'ventas', 'compras']);

        // Estadísticas del usuario
        $totalVentas = $user->ventas()->where('estado', 1)->count();
        $montoVentas = $user->ventas()->where('estado', 1)->sum('total');
        $totalCompras = $user->compras()->where('estado', 1)->count();
        $montoCompras = $user->compras()->where('estado', 1)->sum('total');

        return view('user.show', compact(
            'user',
            'totalVentas',
            'montoVentas',
            'totalCompras',
            'montoCompras'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
      public function edit(User $user)
    {
        $roles = Role::all();
        $sucursales = Sucursal::where('estado', 1)->get();

        // Obtener sucursales asignadas al usuario
        $sucursalesUsuario = $user->sucursales->pluck('id')->toArray();

        // Obtener sucursal principal
        $sucursalPrincipal = $user->sucursales()
            ->wherePivot('es_principal', 1)
            ->first();

        return view('user.edit', compact(
            'user',
            'roles',
            'sucursales',
            'sucursalesUsuario',
            'sucursalPrincipal'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
 public function update(UpdateUserRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            // Preparar datos para actualizar
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Comprobar el password y aplicar el Hash
            if (!empty($request->password)) {
                $data['password'] = Hash::make($request->password);
            }

            // Actualizar usuario
            $user->update($data);

            // Actualizar rol
            $user->syncRoles([$request->role]);

            // Actualizar sucursales
            if ($request->has('sucursales') && is_array($request->sucursales)) {
                // Primero desasociar todas las sucursales
                $user->sucursales()->detach();

                // Luego asociar las nuevas
                foreach ($request->sucursales as $sucursalId) {
                    $esPrincipal = ($request->sucursal_principal == $sucursalId) ? 1 : 0;

                    $user->sucursales()->attach($sucursalId, [
                        'es_principal' => $esPrincipal,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            } else {
                // Si no se seleccionó ninguna sucursal, desasociar todas
                $user->sucursales()->detach();
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuario actualizado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                ->with('error', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
 public function destroy(string $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Verificar si tiene movimientos (ventas o compras)
            $tieneMovimientos = $user->ventas()->count() > 0 || $user->compras()->count() > 0;

            if ($tieneMovimientos) {
                // Solo desactivar si tiene movimientos
                $user->update(['estado' => 0]);
                $message = 'Usuario desactivado exitosamente';
            } else {
                // Si no tiene movimientos, se puede eliminar completamente
                // Eliminar rol
                $rolUser = $user->getRoleNames()->first();
                if ($rolUser) {
                    $user->removeRole($rolUser);
                }

                // Desasociar sucursales
                $user->sucursales()->detach();

                // Eliminar usuario
                $user->delete();
                $message = 'Usuario eliminado exitosamente';
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', $message);

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                ->with('error', 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    public function reactivar(string $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);
            $user->update(['estado' => 1]);

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'Usuario reactivado exitosamente');

        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->route('users.index')
                ->with('error', 'Error al reactivar usuario: ' . $e->getMessage());
        }
    }

    /**
     * Ver usuarios inactivos
     */
    public function inactivos()
    {
        $users = User::with(['roles', 'sucursales'])
            ->where('estado', 0)
            ->get();

        return view('user.inactivos', compact('users'));
    }
}
