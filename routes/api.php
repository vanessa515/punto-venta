<?php

use App\Http\Controllers\SucursalesController;
use App\Http\Controllers\UsuariosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;

// ============================================================
//  ROLES Y PERMISOS (Existentes de tus compañeros)
// ============================================================
Route::get('/roles', [RolesController::class, 'index']);
Route::get('/permisos', [RolesController::class, 'permisos']);

// ============================================================
//  MÓDULO: CATEGORÍAS
// ============================================================
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::get('/categorias/padres', [CategoriaController::class, 'padres']);
Route::post('/categorias', [CategoriaController::class, 'store']);
Route::put('/categorias/{id}', [CategoriaController::class, 'update']);
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

// ============================================================
//  MÓDULO: MARCAS
// ============================================================
Route::get('/marcas', [MarcaController::class, 'index']);
Route::post('/marcas', [MarcaController::class, 'store']);
Route::put('/marcas/{id}', [MarcaController::class, 'update']);
Route::delete('/marcas/{id}', [MarcaController::class, 'destroy']);

// ============================================================
//  MÓDULO: PRODUCTOS & VARIANTES
// ============================================================
Route::get('/productos', [ProductoController::class, 'index']);
// Esta es la que conecta directo con tu método public function datosFormulario()
Route::get('/productos/datos-formulario', [ProductoController::class, 'datosFormulario']); 
Route::get('/productos/buscar-por-codigo', [ProductoController::class, 'buscarPorCodigo']);
Route::post('/productos', [ProductoController::class, 'store']);
Route::put('/productos/{id}', [ProductoController::class, 'update']);
Route::delete('/productos/{id}', [ProductoController::class, 'destroy']);

// Acciones específicas para variantes sueltas
Route::post('/productos/{id}/variantes', [ProductoController::class, 'agregarVariante']);
Route::put('/variantes/{id}', [ProductoController::class, 'actualizarVariante']);
Route::delete('/variantes/{id}', [ProductoController::class, 'eliminarVariante']);

////////////////ROLES Y PERMISOS////////////////////

Route::get('/roles', [RolesController::class, 'index']);

Route::get('/permisos', [RolesController::class, 'permisos']);

Route::post('/permisosinsert', [RolesController::class, 'store']);

Route::put('/permisosupdate', [RolesController::class, 'update']);

Route::delete('/permisosdelete', [RolesController::class, 'destroy']);

////////////////USUARIOS////////////////////

Route::get('/usuarios', [UsuariosController::class, 'index']);

Route::post('/registrousr', [UsuariosController::class, 'store']);

Route::put('/actualizarusr', [UsuariosController::class, 'update']);

Route::delete('/usrdelete', [UsuariosController::class, 'destroy']);

////////////////COMPANIAS////////////////////

Route::get('/sucursales', [SucursalesController::class, 'index']);

Route::post('/sucursales/store', [SucursalesController::class, 'store']);

Route::put('/actualizar/comp', [SucursalesController::class, 'update']);

Route::delete('/eliminar/comp', [SucursalesController::class, 'destroy']);
