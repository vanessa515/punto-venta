<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    // GET /api/marcas
    // Lista todas las marcas con opción de búsqueda por nombre
    public function index(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $marcas = Marca::when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->orderBy('id_marca', 'desc')
            ->get();

        return response()->json([
            'marcas' => $marcas,
        ]);
    }

    // POST /api/marcas
    // Registra una nueva marca en la base de datos
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => 'required|string|max:100|unique:marcas,nombre',
        ]);

        $marca = Marca::create([
            'nombre'  => $datos['nombre'],
            'estatus' => 1, // Activa por defecto al registrarse
        ]);

        return response()->json([
            'mensaje' => 'Marca registrada correctamente',
            'marca'   => $marca,
        ], 201);
    }

    // PUT /api/marcas/{id}
    // Actualiza los datos o el estatus de una marca existente
    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $datos = $request->validate([
            'nombre'  => 'required|string|max:100|unique:marcas,nombre,' . $id . ',id_marca',
            'estatus' => 'sometimes|boolean',
        ]);

        $marca->update($datos);

        return response()->json([
            'mensaje' => 'Marca actualizada correctamente',
            'marca'   => $marca,
        ]);
    }

    // DELETE /api/marcas/{id}
    // Desactivación lógica (borrado suave cambiando el estatus a 0)
    public function destroy($id)
    {
        $marca = Marca::findOrFail($id);
        
        $marca->update(['estatus' => 0]);

        return response()->json([
            'mensaje' => 'Marca desactivada correctamente',
        ]);
    }
}