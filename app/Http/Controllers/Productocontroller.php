<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Models\CodigoBarra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    // GET /api/productos
    // Trae el producto con su categoría, marca y todas sus variantes
    public function index(Request $request)
    {
        $busqueda = $request->query('busqueda');

        $productos = Producto::with(['categoria', 'marca', 'variantes.codigosBarras'])
            ->when($busqueda, function ($query, $busqueda) {
                $query->where('nombre', 'like', "%{$busqueda}%");
            })
            ->orderBy('id_producto', 'desc')
            ->get();

        return response()->json([
            'productos' => $productos,
        ]);
    }

    // GET /api/productos/datos-formulario
    // Categorías y marcas activas, para llenar los <select> del modal
    public function datosFormulario()
    {
        return response()->json([
            'categorias' => \App\Models\Categoria::where('estatus', 1)->orderBy('nombre')->get(['id_categoria', 'nombre']),
            'marcas'     => \App\Models\Marca::where('estatus', 1)->orderBy('nombre')->get(['id_marca', 'nombre']),
        ]);
    }

    // GET /api/productos/buscar-por-codigo?codigo=XXXX
    // Busca una variante por su código de barras (para lectura con
    // pistola o cámara) y regresa el producto completo al que pertenece.
    public function buscarPorCodigo(Request $request)
    {
        $codigo = $request->query('codigo');

        if (!$codigo) {
            return response()->json(['mensaje' => 'Debes enviar un código.'], 422);
        }

        $codigoBarra = CodigoBarra::where('codigo', $codigo)->first();

        if (!$codigoBarra) {
            return response()->json([
                'encontrado' => false,
                'mensaje'    => 'No se encontró ningún producto con ese código.',
            ], 404);
        }

        $variante = ProductoVariante::with(['producto.categoria', 'producto.marca', 'codigosBarras'])
            ->find($codigoBarra->fk_variante);

        return response()->json([
            'encontrado' => true,
            'variante'   => $variante,
        ]);
    }

    // POST /api/productos
    // Crea el producto y, en la misma petición, sus variantes.
    // Si maneja_variantes = false, el frontend debe enviar igual
    // un arreglo "variantes" con UN solo elemento (talla y color en null).
    public function store(Request $request)
    {
        $datos = $request->validate([
            'fk_categoria'        => 'nullable|integer|exists:categorias,id_categoria',
            'fk_marca'            => 'nullable|integer|exists:marcas,id_marca',
            'nombre'              => 'required|string|max:150',
            'descripcion'         => 'nullable|string',
            'unidad_medida'       => 'required|string|max:20',
            'maneja_variantes'    => 'required|boolean',
            'aplica_iva'          => 'required|boolean',
            'imagen_principal'    => 'nullable|image|max:2048',

            'variantes'                    => 'required|array|min:1',
            'variantes.*.sku'              => 'required|string|max:60|distinct|unique:producto_variantes,sku',
            'variantes.*.talla'            => 'nullable|string|max:20',
            'variantes.*.color'            => 'nullable|string|max:40',
            'variantes.*.precio_compra'    => 'required|numeric|min:0',
            'variantes.*.precio_venta'     => 'required|numeric|min:0',
            'variantes.*.codigos'          => 'nullable|array',
            'variantes.*.codigos.*'        => 'nullable|string|max:100|distinct|unique:codigos_barras,codigo',
        ]);

        $producto = DB::transaction(function () use ($datos, $request) {

            $imagenPath = null;
            if ($request->hasFile('imagen_principal')) {
                $imagenPath = $request->file('imagen_principal')->store('productos', 'public');
            }

            $producto = Producto::create([
                'fk_categoria'     => $datos['fk_categoria'] ?? null,
                'fk_marca'         => $datos['fk_marca'] ?? null,
                'nombre'           => $datos['nombre'],
                'descripcion'      => $datos['descripcion'] ?? null,
                'unidad_medida'    => $datos['unidad_medida'],
                'maneja_variantes' => $datos['maneja_variantes'],
                'aplica_iva'       => $datos['aplica_iva'],
                'imagen_principal' => $imagenPath,
            ]);

            foreach ($datos['variantes'] as $v) {
                $variante = ProductoVariante::create([
                    'fk_producto'   => $producto->id_producto,
                    'sku'           => $v['sku'],
                    'talla'         => $v['talla'] ?? null,
                    'color'         => $v['color'] ?? null,
                    'precio_compra' => $v['precio_compra'],
                    'precio_venta'  => $v['precio_venta'],
                ]);

                // Códigos de barras opcionales para esta variante
                if (!empty($v['codigos'])) {
                    foreach ($v['codigos'] as $i => $codigo) {
                        if (!$codigo) continue;
                        CodigoBarra::create([
                            'fk_variante'  => $variante->id_variante,
                            'codigo'       => $codigo,
                            'tipo'         => 'EAN13',
                            'es_principal' => $i === 0,
                        ]);
                    }
                }
            }

            return $producto;
        });

        $producto->load(['categoria', 'marca', 'variantes.codigosBarras']);

        if ($producto->imagen_principal) {
            $producto->imagen_principal = Storage::url($producto->imagen_principal);
        }

        return response()->json([
            'mensaje'  => 'Producto registrado correctamente',
            'producto' => $producto,
        ], 201);
    }

    // PUT /api/productos/{id}
    // Actualiza solo los datos generales del producto.
    // Las variantes se editan aparte con sus propios endpoints
    // (más simple que reescribir todo el árbol en cada guardado).
    public function update(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $datos = $request->validate([
            'fk_categoria'      => 'nullable|integer|exists:categorias,id_categoria',
            'fk_marca'          => 'nullable|integer|exists:marcas,id_marca',
            'nombre'            => 'required|string|max:150',
            'descripcion'       => 'nullable|string',
            'unidad_medida'     => 'required|string|max:20',
            'aplica_iva'        => 'required|boolean',
            'estatus'           => 'sometimes|boolean',
            'imagen_principal'  => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen_principal')) {
            $imagenPath = $request->file('imagen_principal')->store('productos', 'public');
            $datos['imagen_principal'] = $imagenPath;
        }

        $producto->update($datos);

        if ($producto->imagen_principal) {
            $producto->imagen_principal = Storage::url($producto->imagen_principal);
        }

        return response()->json([
            'mensaje'  => 'Producto actualizado correctamente',
            'producto' => $producto,
        ]);
    }

    // DELETE /api/productos/{id}
    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        $producto->update(['estatus' => 0]);

        return response()->json([
            'mensaje' => 'Producto desactivado correctamente',
        ]);
    }

    // POST /api/productos/{id}/variantes
    // Agrega una nueva variante a un producto que ya existe
    // (ej: agregar la talla "XL" a una playera ya creada)
    public function agregarVariante(Request $request, $id)
    {
        $producto = Producto::findOrFail($id);

        $datos = $request->validate([
            'sku'           => 'required|string|max:60|unique:producto_variantes,sku',
            'talla'         => 'nullable|string|max:20',
            'color'         => 'nullable|string|max:40',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
        ]);

        $variante = $producto->variantes()->create($datos);

        return response()->json([
            'mensaje'  => 'Variante agregada correctamente',
            'variante' => $variante,
        ], 201);
    }

    // PUT /api/variantes/{id}
    public function actualizarVariante(Request $request, $id)
    {
        $variante = ProductoVariante::findOrFail($id);

        $datos = $request->validate([
            'sku'           => 'required|string|max:60|unique:producto_variantes,sku,' . $id . ',id_variante',
            'talla'         => 'nullable|string|max:20',
            'color'         => 'nullable|string|max:40',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta'  => 'required|numeric|min:0',
            'estatus'       => 'sometimes|boolean',
        ]);

        $variante->update($datos);

        return response()->json([
            'mensaje'  => 'Variante actualizada correctamente',
            'variante' => $variante,
        ]);
    }

    // DELETE /api/variantes/{id}
    public function eliminarVariante($id)
    {
        $variante = ProductoVariante::findOrFail($id);
        $variante->update(['estatus' => 0]);

        return response()->json([
            'mensaje' => 'Variante desactivada correctamente',
        ]);
    }
}