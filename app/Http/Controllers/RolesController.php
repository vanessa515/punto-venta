<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Roles;
use App\Models\RolesPermisos;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    public function index(Request $request){

       $busqueda = $request ->get("busqueda");

       $roles = Roles::where("nombre","like","%".$busqueda."%")
        ->orderBy("id_rol", "asc")
        ->get();

       return response()->json([
            'roles' => $roles->map(function ($rol) {
                return [
                    'id_rol' => $rol->id_rol,
                    'nombre' => $rol->nombre,
                    'created_at' => $rol->created_at->format('Y/m/d H:i:s'),
                    'permisos' => $rol->permisos->map(function ($permiso) {
                        return [
                            'id_permiso' => $permiso->id_permiso,
                            'nombre' => $permiso->nombre,
                        ];
                    }),
                ];
            })
        ]);

    }

  public function permisos(){
    
        $permisos = Permiso::all();

        $agrupados = $permisos
            ->groupBy(function ($permiso) {

                $partes = explode('_', $permiso->nombre);

                return end($partes);
            })
            ->map(function ($permisos, $modulo) {

                return [
                    'name' => ucfirst($modulo),
                    'permisos' => $permisos->map(function ($permiso) {

                        return [
                            'id_permiso' => $permiso->id_permiso,
                            'nombre' => $permiso->nombre,
                        ];
                    })->values(),
                ];
            })
            ->values();

        return response()->json([
            'permisos' => $agrupados,
        ]);
    }

 public function store(Request $request)
    {
        DB::beginTransaction();

        try {

        $request->validate(
            [
                'nombre' => 'required|unique:roles,nombre',
            ],
            [
                'nombre.unique' => 'Ya existe un rol con ese nombre.',
                'nombre.required' => 'Debes capturar el nombre del rol.'
            ]
        );

            $rol = Roles::create([
                'nombre' => $request['nombre'],
            ]);

            foreach ($request['permisos'] as $permiso) {

                RolesPermisos::create([
                    'fk_rol' => $rol->id_rol,
                    'fk_permiso' => $permiso
                ]);

            }

            DB::commit();

            return response()->json([
                'success' => true
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);

        }
    }


   public function update(Request $request)
    {
        $id = $request->id;

        $rol = Roles::findOrFail($id);

        $existe = Roles::where('nombre', $request->nombre)
            ->where('id_rol', '!=', $id)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un rol con ese nombre.'
            ], 422);
        }

        $rol->nombre = $request->nombre;
        $rol->save();

        DB::table('roles_permisos')
            ->where('fk_rol', $id)
            ->delete();

        foreach ($request->permisos as $permiso) {
            DB::table('roles_permisos')->insert([
                'fk_rol' => $id,
                'fk_permiso' => $permiso
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Rol actualizado correctamente'
        ]);
    }

    public function destroy(Request $request)
    {
        $id = $request->id;

        DB::table('roles_permisos')
            ->where('fk_rol', $id)
            ->delete();

        Roles::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rol eliminado correctamente'
        ]);
    }

}