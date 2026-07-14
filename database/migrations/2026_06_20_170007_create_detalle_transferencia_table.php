<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_transferencia', function (Blueprint $table) {
            $table->id('pk_detalle')->autoIncrement();

            // FK a la cabecera del traspaso
            $table->unsignedBigInteger('fk_transferencia');

            // FK a producto — sin constraint hasta que ese módulo exista
            $table->unsignedBigInteger('fk_producto');

            $table->decimal('cantidad', 10, 2);

            $table->timestamps();

            // FK a transferencia (nombre corregido, antes era transferencias_producto)
            $table->foreign('fk_transferencia')
                  ->references('pk_transferencia')
                  ->on('transferencia')
                  ->onDelete('cascade'); // Si se borra la cabecera, se borran los detalles

            // Descomentar cuando los compañeros suban el módulo de producto:
            // $table->foreign('fk_producto')->references('pk_producto')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_transferencia');
    }
};
