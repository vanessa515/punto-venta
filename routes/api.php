<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\TransferenciaController;

Route::get('/roles', [RolesController::class, 'index']);

Route::get('/permisos', [RolesController::class, 'permisos']);

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('auth:sanctum')->group(function () {

        // ── INVENTARIO ──────────────────────────────────────────────
        Route::prefix('inventario')->group(function () {
            Route::get('/',         [InventarioController::class, 'mostrar']);  // Listar stock
            Route::get('/kardex',   [InventarioController::class, 'kardex']);   // Historial de un producto
            Route::post('/ajustar', [InventarioController::class, 'ajustar']);  // Entrada / salida / ajuste manual
        });

        // ── TRANSFERENCIAS ──────────────────────────────────────────
        Route::prefix('transferencias')->group(function () {
            Route::get('/',                 [TransferenciaController::class, 'mostrar']);  // Historial
            Route::post('/',                [TransferenciaController::class, 'crear']);    // Nuevo traspaso (pendiente)
            Route::put('/{id}/enviar',      [TransferenciaController::class, 'enviar']);   // pendiente → en_transito
            Route::put('/{id}/recibir',     [TransferenciaController::class, 'recibir']); // en_transito → completada
            Route::put('/{id}/cancelar',    [TransferenciaController::class, 'cancelar']); // → cancelada
        });

    });

});