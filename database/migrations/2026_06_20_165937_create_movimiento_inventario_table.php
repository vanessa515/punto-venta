<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movimiento_inventario', function (Blueprint $table) {
            $table->id('pk_movimiento')->autoIncrement();

            // FK a sucursal y producto — sin constraint hasta que esos módulos existan
            $table->unsignedBigInteger('fk_sucursal');
            $table->unsignedBigInteger('fk_producto');

            // Quién hizo el movimiento
            $table->unsignedBigInteger('fk_usuario')->nullable();

            // Si el tipo es 'transferencia', aquí se guarda el folio del traspaso
            $table->unsignedBigInteger('fk_transferencia')->nullable();

            // Detalles del kardex
            $table->enum('tipo_movimiento', ['entrada', 'salida', 'ajuste', 'transferencia']);
            $table->decimal('cantidad',       10, 2);
            $table->decimal('costo_unitario', 10, 2)->nullable(); // Para calcular el valor total del movimiento

            // Rastreabilidad: "Venta #105", "Traspaso #12", "Ajuste manual", etc.
            $table->string('referencia')->nullable();

            $table->timestamps();

            // FKs que sí existen desde el inicio
            $table->foreign('fk_usuario')
                  ->references('id')
                  ->on('users');

            // FK a la tabla transferencia (nombre corregido, antes era transferencias_producto)
            $table->foreign('fk_transferencia')
                  ->references('pk_transferencia')
                  ->on('transferencia');

            // Descomentar cuando los compañeros suban sucursal y producto:
            // $table->foreign('fk_sucursal')->references('pk_sucursal')->on('sucursal');
            // $table->foreign('fk_producto')->references('pk_producto')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventario');
    }
};
