<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // GET /api/categorias
    // Lista todas las categorías con búsqueda por nombre
    public function index(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $categorias = Categoria::with('padre')
            ->when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->orderBy('id_categoria', 'desc')
            ->get();

        return response()->json([
            'categorias' => $categorias,
        ]);
    }

    // GET /api/categorias/padres
    // Devuelve las categorías activas para elegir como padre
    public function padres()
    {
        $categorias = Categoria::where('estatus', 1)
            ->orderBy('nombre')
            ->get(['id_categoria', 'nombre']);

        return response()->json([
            'categorias' => $categorias,
        ]);
    }

    // POST /api/categorias
    // Registra una nueva categoría
    public function store(Request $request)
    {
        $datos = $request->validate([
            'fk_categoria_padre' => 'nullable|integer|exists:categorias,id_categoria',
            'nombre'             => 'required|string|max:150|unique:categorias,nombre',
            'descripcion'        => 'nullable|string|max:255',
        ]);

        $categoria = Categoria::create([
            'fk_categoria_padre' => $datos['fk_categoria_padre'] ?? null,
            'nombre'             => $datos['nombre'],
            'descripcion'        => $datos['descripcion'] ?? null,
            'estatus'            => 1,
        ]);

        return response()->json([
            'mensaje'   => 'Categoría registrada correctamente',
            'categoria' => $categoria,
        ], 201);
    }

    // PUT /api/categorias/{id}
    // Actualiza una categoría existente
    public function update(Request $request, $id)
    {
        $categoria = Categoria::findOrFail($id);

        $datos = $request->validate([
            'fk_categoria_padre' => 'nullable|integer|exists:categorias,id_categoria',
            'nombre'             => 'required|string|max:150|unique:categorias,nombre,' . $id . ',id_categoria',
            'descripcion'        => 'nullable|string|max:255',
            'estatus'            => 'sometimes|boolean',
        ]);

        $categoria->update($datos);

        return response()->json([
            'mensaje'   => 'Categoría actualizada correctamente',
            'categoria' => $categoria,
        ]);
    }

    // DELETE /api/categorias/{id}
    // Desactivación lógica (estatus = 0)
    public function destroy($id)
    {
        $categoria = Categoria::findOrFail($id);
        $categoria->update(['estatus' => 0]);

        return response()->json([
            'mensaje' => 'Categoría desactivada correctamente',
        ]);
    }
}
