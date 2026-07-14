<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transferencia', function (Blueprint $table) {
            $table->id('pk_transferencia')->autoIncrement();

            // Sucursales involucradas — sin constraint hasta que ese módulo exista
            $table->unsignedBigInteger('fk_sucursal_origen');
            $table->unsignedBigInteger('fk_sucursal_destino');

            // Quién creó el traspaso
            $table->unsignedBigInteger('fk_usuario_solicita');

            // Quién lo recibe — se llena automáticamente al ejecutar recibir()
            $table->unsignedBigInteger('fk_usuario_recibe')->nullable();

            // Máquina de estados
            $table->enum('estado', ['pendiente', 'en_transito', 'completada', 'cancelada'])
                  ->default('pendiente');

            $table->string('notas')->nullable();

            $table->timestamps();

            // FKs a users que sí existen desde el inicio
            $table->foreign('fk_usuario_solicita')
                  ->references('id')
                  ->on('users');
            $table->foreign('fk_usuario_recibe')
                  ->references('id')
                  ->on('users');

            // Descomentar cuando los compañeros suban el módulo de sucursal:
            // $table->foreign('fk_sucursal_origen')->references('pk_sucursal')->on('sucursal');
            // $table->foreign('fk_sucursal_destino')->references('pk_sucursal')->on('sucursal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transferencia');
    }
};
