<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\MovimientoInventario;
use Exception;

class InventarioService
{
    /**
     * Mueve stock en la tabla inventario y registra el movimiento en el kardex.
     *
     * @param  int        $fk_sucursal       Sucursal donde ocurre el movimiento
     * @param  int        $fk_producto       Producto que se mueve
     * @param  float      $cantidad          Cuánto se mueve (siempre positivo)
     * @param  string     $tipo_movimiento   entrada | salida | ajuste | transferencia
     * @param  int        $fk_usuario        Quién ejecuta el movimiento
     * @param  string     $referencia        Texto descriptivo del origen
     * @param  float|null $costo_unitario    Costo por unidad en este movimiento (opcional)
     * @param  int|null   $fk_transferencia  Folio si viene de un traspaso
     *
     * @throws Exception  Si no hay suficiente stock para una salida o transferencia
     */
    public function moverStock(
        int    $fk_sucursal,
        int    $fk_producto,
        float  $cantidad,
        string $tipo_movimiento,
        int    $fk_usuario,
        string $referencia       = 'Movimiento manual',
        ?float $costo_unitario   = null,
        ?int   $fk_transferencia = null
    ): void {
        // 1. Buscar el registro de inventario o crearlo con valores en cero
        $inventario = Inventario::firstOrCreate(
            ['fk_sucursal' => $fk_sucursal, 'fk_producto' => $fk_producto],
            ['cantidad' => 0, 'stock_minimo' => 0, 'costo_unitario' => 0]
        );

        // 2. Aplicar la operación según el tipo
        if (in_array($tipo_movimiento, ['salida', 'transferencia'])) {
            if ($inventario->cantidad < $cantidad) {
                throw new Exception(
                    "Stock insuficiente. Disponible: {$inventario->cantidad}, requerido: {$cantidad}."
                );
            }
            $inventario->cantidad -= $cantidad;
        } else {
            // entrada o ajuste: suma
            $inventario->cantidad += $cantidad;

            // Si viene con costo, actualizamos el costo unitario de referencia en inventario
            if ($costo_unitario !== null) {
                $inventario->costo_unitario = $costo_unitario;
            }
        }

        $inventario->save();

        // 3. Registrar en el kardex
        MovimientoInventario::create([
            'fk_sucursal'      => $fk_sucursal,
            'fk_producto'      => $fk_producto,
            'fk_usuario'       => $fk_usuario,
            'fk_transferencia' => $fk_transferencia,
            'tipo_movimiento'  => $tipo_movimiento,
            'cantidad'         => $cantidad,
            'costo_unitario'   => $costo_unitario,
            'referencia'       => $referencia,
        ]);
    }
}
