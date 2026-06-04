<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use App\Models\Roles;
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

}