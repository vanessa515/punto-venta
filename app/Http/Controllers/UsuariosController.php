<?php

namespace App\Http\Controllers;


use App\Models\Sucursales;
use App\Models\Personal;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Storage;


class UsuariosController extends Controller
{

    public function index(Request $request){

       $busqueda = $request ->get("busqueda");

       $filtro = $request->get('filtro','users.name');

       $usuarios = DB::table('users')
        ->join('roles','users.fk_rol','=','roles.id_rol')
        ->join('dpersonales','users.fk_persona','=','dpersonales.id_dpersonal')
        ->select(
            'users.id',
            'users.name as username',
            'users.email as correo',
            'users.avatar',
            'users.created_at',
            'roles.id_rol',
            'roles.nombre as rol',
            'users.estatus',
            'dpersonales.id_dpersonal',
            'dpersonales.nombre as nombre_personal',
            'dpersonales.fk_sucursal as id_sucursal',
            'dpersonales.cp',
            'dpersonales.direccion',
            'dpersonales.telefono'
            )
        ->where($filtro,"like","%".$busqueda."%")
        ->orderBy("id", "asc")
        ->get();

        $roles = DB::table('roles')->select('id_rol','nombre as nombre_rol')->get();

        $sucursales = DB::table('sucursales')->select('id_sucursal','nombre as nombre_sucursal','email','telefono','direccion')->get();

       return response()->json([
            'usuarios' => $usuarios,
            'roles' => $roles,
            'sucursales' => $sucursales,
        ]);

    }

    public function store(Request $request)
    {

    try {

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:users,name',
            'nombre_personal' => 'required|string|max:255',
            'cp' => 'required',
            'direccion' => 'required',
            'telefono' => 'required',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'fk_rol' => 'required|integer|exists:roles,id_rol',
            'fk_sucursal' => 'required|integer',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ],[
            'nombre.unique' => 'El username ya existe, intenta con otro.',
            'email.unique' => 'El correo ya está registrado.',
        ]);

        $persona = new Personal();
        $persona->nombre = $validatedData['nombre_personal'];
        $persona->cp = $validatedData['cp'];
        $persona->direccion = $validatedData['direccion'];
        $persona->telefono = $validatedData['telefono'];
        $persona->fk_sucursal = $validatedData['fk_sucursal'];
        $persona->save();


        $user = new User();
        $user->name = $validatedData['nombre'];
        $user->email = $validatedData['email'];
        $user->password = bcrypt($validatedData['password']);
        $user->fk_rol = $validatedData['fk_rol'];
        $user->estatus = 1;
        $user->fk_persona = $persona->id_dpersonal;

        if ($request->hasFile('avatar')) {

            $avatar = $request->file('avatar');

            $nombreAvatar = time() . '_' . $avatar->getClientOriginalName();

            $user->avatar = $avatar->storeAs(
                'avatars',
                $nombreAvatar,
                'public'
            );
        }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'usuario' => $user
            ], 201);

            } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request)
    {
        try {

            $validatedData = $request->validate([
                'id' => 'required|integer|exists:users,id',

                'nombre' => 'required|string|max:255|unique:users,name,' . $request->id,
                'nombre_personal' => 'required|string|max:255',
                'cp' => 'required',
                'direccion' => 'required',
                'telefono' => 'required',
                'email' => 'required|string|email|max:255|unique:users,email,' . $request->id,
                'password' => 'nullable|string|min:8',
                'fk_rol' => 'required|integer|exists:roles,id_rol',
                'estatus' => 'required|integer',
                'fk_sucursal' => 'required|integer',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'nombre.unique' => 'El username ya existe, intenta con otro.',
                'email.unique' => 'El correo ya está registrado.',
            ]);

            return DB::transaction(function () use ($validatedData, $request) {

                $user = User::findOrFail($validatedData['id']);

                $user->name = $validatedData['nombre'];
                $user->email = $validatedData['email'];
                $user->fk_rol = $validatedData['fk_rol'];
                $user->estatus = $validatedData['estatus'];

                if (!empty($validatedData['password'])) {
                    $user->password = Hash::make($validatedData['password']);
                }

                if ($request->hasFile('avatar')) {
                    if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                        Storage::disk('public')->delete($user->avatar);
                    }

                    $avatar = $request->file('avatar');
                    $nombreAvatar = time() . '_' . $avatar->getClientOriginalName();

                    $user->avatar = $avatar->storeAs('avatars', $nombreAvatar, 'public');
                }

                $user->save();

                $persona = Personal::findOrFail($user->fk_persona);

                $persona->nombre = $validatedData['nombre_personal'];
                $persona->cp = $validatedData['cp'];
                $persona->direccion = $validatedData['direccion'];
                $persona->telefono = $validatedData['telefono'];
                $persona->fk_sucursal = $validatedData['fk_sucursal'];

                $persona->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario actualizado exitosamente',
                    'usuario' => $user
                ]);

            });

        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->id;

        $usuario = DB::table('users')
            ->select('avatar')
            ->where('id', $id)
            ->first();

        if ($usuario && $usuario->avatar) {

            if (Storage::disk('public')->exists($usuario->avatar)) {
                Storage::disk('public')->delete($usuario->avatar);
            }

        }

        DB::table('users')
            ->where('id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }

} 