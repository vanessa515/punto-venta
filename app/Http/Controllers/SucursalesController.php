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


class SucursalesController extends Controller
{

    public function index(Request $request){

       $busqueda = $request ->get("busqueda");

       $sucursales = DB::table('sucursales')
        ->select(
            'id_sucursal',
            'nombre',
            'rfc',
            'email',
            'telefono',
            'direccion',
            'logo'
    
            )
        ->where('nombre',"like","%".$busqueda."%")
        ->orderBy("id_sucursal", "asc")
        ->get();

       return response()->json([
            'sucursales' => $sucursales,
        ]);

    }

    public function store(Request $request)
    {

    try {

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:sucursales,nombre',
            'rfc' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:sucursales,email',
            'direccion' => 'required',
            'telefono' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ],[
            'nombre.unique' => 'La empresa ya existe, intenta con otro.',
            'email.unique' => 'El correo ya está registrado.',
        ]);


        $sucursales = new Sucursales();
        $sucursales->nombre = $validatedData['nombre'];
        $sucursales->email = $validatedData['email'];
        $sucursales->rfc = $validatedData['rfc'];
        $sucursales->direccion = $validatedData['direccion'];
        $sucursales->telefono = $validatedData['telefono'];

        if ($request->hasFile('logo')) {

            $logo = $request->file('logo');

            $nombrelogo = time() . '_' . $logo->getClientOriginalName();

            $sucursales->logo = $logo->storeAs(
                'logo',
                $nombrelogo,
                'public'
            );
        }

            $sucursales->save();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'sucursales' => $sucursales
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
                'id' => 'required|integer|exists:sucursales,id_sucursal',

                'nombre' => 'required|string|max:255|unique:sucursales,nombre,' . $request->id . ',id_sucursal',
                'direccion' => 'required',
                'telefono' => 'required',
                'rfc' => 'required|string|max:50',
                'email' => 'required|string|email|max:255|unique:sucursales,email,' . $request->id . ',id_sucursal',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            ], [
                'nombre.unique' => 'La sucursal ya existe, intenta con otra.',
                'email.unique' => 'El correo ya está registrado.',
            ]);

            return DB::transaction(function () use ($validatedData, $request) {

                $sucursales = Sucursales::findOrFail($validatedData['id']);

                $sucursales->nombre = $validatedData['nombre'];
                $sucursales->email = $validatedData['email'];
                $sucursales->rfc = $validatedData['rfc'];
                $sucursales->direccion = $validatedData['direccion'];
                $sucursales->telefono = $validatedData['telefono'];


                if ($request->hasFile('logo')) {
                    if ($sucursales->logo && Storage::disk('public')->exists($sucursales->logo)) {
                        Storage::disk('public')->delete($sucursales->logo);
                    }

                    $logo = $request->file('logo');
                    $nombrelogo = time() . '_' . $logo->getClientOriginalName();

                    $sucursales->logo = $logo->storeAs('avatars', $nombrelogo, 'public');
                }

                $sucursales->save();


                return response()->json([
                    'success' => true,
                    'message' => 'sucursal actualizada exitosamente',
                    'sucursales' => $sucursales
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

        $sucursales = DB::table('sucursales')
            ->select('logo')
            ->where('id_sucursal', $id)
            ->first();

        if ($sucursales && $sucursales->logo) {

            if (Storage::disk('public')->exists($sucursales->logo)) {
                Storage::disk('public')->delete($sucursales->logo);
            }

        }

        DB::table('sucursales')
            ->where('id_sucursal', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sucursal eliminada correctamente'
        ]);
    }

} 