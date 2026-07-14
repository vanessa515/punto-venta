<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventario;
use App\Models\MovimientoInventario;
use App\Services\InventarioService;
use App\Http\Requests\AjusteInventarioRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class InventarioController extends Controller
{
    protected InventarioService $inventarioService;

    public function __construct(InventarioService $inventarioService)
    {
        $this->inventarioService = $inventarioService;
    }

    // ─────────────────────────────────────────────────────────────
    // MOSTRAR
    // ─────────────────────────────────────────────────────────────

    /**
     * Listar el inventario completo.
     * Filtro opcional por sucursal: GET /api/inventario?fk_sucursal=1
     */
    public function mostrar(Request $request)
    {
        $consulta = Inventario::query();

        if ($request->filled('fk_sucursal')) {
            $consulta->where('fk_sucursal', $request->fk_sucursal);
        }

        // Marca los registros con stock bajo para que el frontend los resalte
        $inventario = $consulta->get()->map(function ($item) {
            $item->bajo_stock = $item->bajo_stock; // accesor del modelo
            return $item;
        });

        return response()->json($inventario, 200);
    }

    /**
     * Kardex: historial de movimientos de un producto en una sucursal.
     * GET /api/inventario/kardex?fk_sucursal=1&fk_producto=5
     */
    public function kardex(Request $request)
    {
        $request->validate([
            'fk_sucursal' => ['required', 'integer'],
            'fk_producto' => ['required', 'integer'],
        ], [
            'fk_sucursal.required' => 'La sucursal es obligatoria.',
            'fk_producto.required' => 'El producto es obligatorio.',
        ]);

        $movimientos = MovimientoInventario::where('fk_sucursal', $request->fk_sucursal)
            ->where('fk_producto', $request->fk_producto)
            ->with('usuario:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($movimientos, 200);
    }

    // ─────────────────────────────────────────────────────────────
    // AJUSTE MANUAL (entrada, salida o ajuste)
    // ─────────────────────────────────────────────────────────────

    /**
     * Registrar una entrada, salida o ajuste manual de inventario.
     * POST /api/inventario/ajustar
     */
    public function ajustar(AjusteInventarioRequest $request)
    {
        try {
            DB::transaction(function () use ($request) {
                $this->inventarioService->moverStock(
                    fk_sucursal:      $request->fk_sucursal,
                    fk_producto:      $request->fk_producto,
                    cantidad:         $request->cantidad,
                    tipo_movimiento:  $request->tipo_movimiento,
                    fk_usuario:       Auth::id(),
                    referencia:       $request->referencia ?? 'Ajuste manual de inventario',
                    costo_unitario:   $request->costo_unitario,
                );
            });

            return response()->json(['mensaje' => 'Inventario actualizado correctamente.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
