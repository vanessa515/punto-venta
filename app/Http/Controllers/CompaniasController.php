<?php

namespace App\Http\Controllers;


use App\Models\Companias;
use App\Models\Personal;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Storage;


class CompaniasController extends Controller
{

    public function index(Request $request){

       $busqueda = $request ->get("busqueda");

       $companias = DB::table('companias')
        ->select(
            'id_compania',
            'nombre',
            'rfc',
            'email',
            'telefono',
            'direccion'
    
            )
        ->where('nombre',"like","%".$busqueda."%")
        ->orderBy("id_compania", "asc")
        ->get();


       return response()->json([
            'companias' => $companias,
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
            'fk_compania' => 'required|integer',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ],[
            'nombre.unique' => 'El username ya existe, intenta con otro.',
            'email.unique' => 'El correo ya está registrado.',
        ]);


        $user = new User();
        $user->name = $validatedData['nombre'];
        $user->email = $validatedData['email'];
        $user->password = bcrypt($validatedData['password']);
        $user->fk_rol = $validatedData['fk_rol'];
        $user->estatus = 1;

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
                'fk_compania' => 'required|integer',
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
                $persona->fk_compania = $validatedData['fk_compania'];

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