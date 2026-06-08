<?php

use App\Http\Controllers\UsuariosController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;

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