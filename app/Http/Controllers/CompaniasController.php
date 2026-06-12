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
            'direccion',
            'logo'
    
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
            'nombre' => 'required|string|max:255|unique:companias,nombre',
            'rfc' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:companias,email',
            'direccion' => 'required',
            'telefono' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ],[
            'nombre.unique' => 'La empresa ya existe, intenta con otro.',
            'email.unique' => 'El correo ya está registrado.',
        ]);


        $companias = new Companias();
        $companias->nombre = $validatedData['nombre'];
        $companias->email = $validatedData['email'];
        $companias->rfc = $validatedData['rfc'];
        $companias->direccion = $validatedData['direccion'];
        $companias->telefono = $validatedData['telefono'];

        if ($request->hasFile('logo')) {

            $logo = $request->file('logo');

            $nombrelogo = time() . '_' . $logo->getClientOriginalName();

            $companias->logo = $logo->storeAs(
                'logo',
                $nombrelogo,
                'public'
            );
        }

            $companias->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'companias' => $companias
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
                'id' => 'required|integer|exists:companias,id_compania',

                'nombre' => 'required|string|max:255|unique:companias,nombre,' . $request->id . ',id_compania',
                'direccion' => 'required',
                'telefono' => 'required',
                'rfc' => 'required|string|max:50',
                'email' => 'required|string|email|max:255|unique:companias,email,' . $request->id . ',id_compania',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'nombre.unique' => 'La compañía ya existe, intenta con otra.',
                'email.unique' => 'El correo ya está registrado.',
            ]);

            return DB::transaction(function () use ($validatedData, $request) {

                $compania = Companias::findOrFail($validatedData['id']);

                $compania->nombre = $validatedData['nombre'];
                $compania->email = $validatedData['email'];
                $compania->rfc = $validatedData['rfc'];
                $compania->direccion = $validatedData['direccion'];
                $compania->telefono = $validatedData['telefono'];


                if ($request->hasFile('logo')) {
                    if ($compania->logo && Storage::disk('public')->exists($compania->logo)) {
                        Storage::disk('public')->delete($compania->logo);
                    }

                    $logo = $request->file('logo');
                    $nombrelogo = time() . '_' . $logo->getClientOriginalName();

                    $compania->logo = $logo->storeAs('avatars', $nombrelogo, 'public');
                }

                $compania->save();


                return response()->json([
                    'success' => true,
                    'message' => 'Compania actualizada exitosamente',
                    'companias' => $compania
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

        $compania = DB::table('companias')
            ->select('logo')
            ->where('id_compania', $id)
            ->first();

        if ($compania && $compania->logo) {

            if (Storage::disk('public')->exists($compania->logo)) {
                Storage::disk('public')->delete($compania->logo);
            }

        }

        DB::table('companias')
            ->where('id_compania', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Compañia eliminada correctamente'
        ]);
    }

} 