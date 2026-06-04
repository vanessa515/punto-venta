<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RolesController;

Route::get('/roles', [RolesController::class, 'index']);

Route::get('/permisos', [RolesController::class, 'permisos']);