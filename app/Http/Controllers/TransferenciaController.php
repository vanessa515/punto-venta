<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\DetalleTransferencia;
use App\Services\InventarioService;
use App\Http\Requests\CrearTransferenciaRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class TransferenciaController extends Controller
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
     * Listar todas las transferencias con sus detalles y usuarios.
     * GET /api/transferencias
     */
    public function mostrar()
    {
        $transferencias = Transferencia::with([
                'detalles',
                'usuarioSolicita:id,name',
                'usuarioRecibe:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($transferencias, 200);
    }

    // ─────────────────────────────────────────────────────────────
    // CREAR — estado: pendiente
    // El stock NO se mueve todavía. Solo se arma la lista de empaque.
    // ─────────────────────────────────────────────────────────────

    /**
     * POST /api/transferencias
     */
    public function crear(CrearTransferenciaRequest $request)
    {
        try {
            $transferencia = DB::transaction(function () use ($request) {

                // 1. Cabecera del traspaso
                $transferencia = Transferencia::create([
                    'fk_sucursal_origen'  => $request->fk_sucursal_origen,
                    'fk_sucursal_destino' => $request->fk_sucursal_destino,
                    'fk_usuario_solicita' => Auth::id(),
                    'estado'              => 'pendiente',
                    'notas'               => $request->notas,
                ]);

                // 2. Detalle por cada producto
                foreach ($request->detalles as $detalle) {
                    DetalleTransferencia::create([
                        'fk_transferencia' => $transferencia->pk_transferencia,
                        'fk_producto'      => $detalle['fk_producto'],
                        'cantidad'         => $detalle['cantidad'],
                    ]);
                }

                return $transferencia->load(['detalles', 'usuarioSolicita:id,name']);
            });

            return response()->json([
                'mensaje' => 'Traspaso creado y listo para envío.',
                'datos'   => $transferencia,
            ], 201);

        } catch (Exception $e) {
            return response()->json(['error' => 'Error al crear el traspaso: ' . $e->getMessage()], 400);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // ENVIAR — pendiente → en_transito
    // Se descuenta el stock de la sucursal origen.
    // ─────────────────────────────────────────────────────────────

    /**
     * PUT /api/transferencias/{id}/enviar
     */
    public function enviar(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transferencia = Transferencia::with('detalles')->findOrFail($id);

                if ($transferencia->estado !== 'pendiente') {
                    throw new Exception(
                        "Solo se puede enviar un traspaso en estado 'pendiente'. Estado actual: {$transferencia->estado}."
                    );
                }

                // Descontar del origen
                foreach ($transferencia->detalles as $detalle) {
                    $this->inventarioService->moverStock(
                        fk_sucursal:      $transferencia->fk_sucursal_origen,
                        fk_producto:      $detalle->fk_producto,
                        cantidad:         $detalle->cantidad,
                        tipo_movimiento:  'transferencia',
                        fk_usuario:       Auth::id(),
                        referencia:       "Envío Traspaso #{$transferencia->pk_transferencia}",
                        fk_transferencia: $transferencia->pk_transferencia,
                    );
                }

                $transferencia->update(['estado' => 'en_transito']);
            });

            return response()->json(['mensaje' => 'Traspaso enviado. Stock descontado del origen.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // RECIBIR — en_transito → completada
    // Se suma el stock en la sucursal destino y se registra quién recibió.
    // ─────────────────────────────────────────────────────────────

    /**
     * PUT /api/transferencias/{id}/recibir
     */
    public function recibir(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transferencia = Transferencia::with('detalles')->findOrFail($id);

                if ($transferencia->estado !== 'en_transito') {
                    throw new Exception(
                        "Solo se puede recibir un traspaso en estado 'en_transito'. Estado actual: {$transferencia->estado}."
                    );
                }

                // Sumar al destino
                foreach ($transferencia->detalles as $detalle) {
                    $this->inventarioService->moverStock(
                        fk_sucursal:      $transferencia->fk_sucursal_destino,
                        fk_producto:      $detalle->fk_producto,
                        cantidad:         $detalle->cantidad,
                        tipo_movimiento:  'entrada',
                        fk_usuario:       Auth::id(),
                        referencia:       "Recepción Traspaso #{$transferencia->pk_transferencia}",
                        fk_transferencia: $transferencia->pk_transferencia,
                    );
                }

                // Guardar quién recibió — automáticamente el usuario autenticado
                $transferencia->update([
                    'estado'            => 'completada',
                    'fk_usuario_recibe' => Auth::id(),
                ]);
            });

            return response()->json(['mensaje' => 'Traspaso recibido. Stock sumado al destino.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // CANCELAR — pendiente o en_transito → cancelada
    // Si ya estaba en_transito, se devuelve el stock al origen.
    // ─────────────────────────────────────────────────────────────

    /**
     * PUT /api/transferencias/{id}/cancelar
     */
    public function cancelar(int $id)
    {
        try {
            DB::transaction(function () use ($id) {
                $transferencia = Transferencia::with('detalles')->findOrFail($id);

                if ($transferencia->estado === 'completada') {
                    throw new Exception('No se puede cancelar un traspaso ya completado.');
                }
                if ($transferencia->estado === 'cancelada') {
                    throw new Exception('Este traspaso ya está cancelado.');
                }

                // Si el camión ya había salido, devolvemos el stock al origen
                if ($transferencia->estado === 'en_transito') {
                    foreach ($transferencia->detalles as $detalle) {
                        $this->inventarioService->moverStock(
                            fk_sucursal:      $transferencia->fk_sucursal_origen,
                            fk_producto:      $detalle->fk_producto,
                            cantidad:         $detalle->cantidad,
                            tipo_movimiento:  'entrada',
                            fk_usuario:       Auth::id(),
                            referencia:       "Devolución por Cancelación Traspaso #{$transferencia->pk_transferencia}",
                            fk_transferencia: $transferencia->pk_transferencia,
                        );
                    }
                }
                // Si estaba 'pendiente', no se había movido stock, solo cambia estado

                $transferencia->update(['estado' => 'cancelada']);
            });

            return response()->json(['mensaje' => 'Traspaso cancelado correctamente.'], 200);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
