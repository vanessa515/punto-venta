<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventario', function (Blueprint $table) {
            $table->id('pk_inventario')->autoIncrement();

            // FK a sucursal y producto — módulos pendientes de otros compañeros.
            // Se declaran sin constraint hasta que esas tablas existan.
            $table->unsignedBigInteger('fk_sucursal');
            $table->unsignedBigInteger('fk_producto');

            // Stock actual y límites
            $table->decimal('cantidad',      10, 2)->default(0);
            $table->decimal('stock_minimo',  10, 2)->default(0);
            $table->decimal('stock_maximo',  10, 2)->nullable();

            // Costo de referencia para valorización del inventario
            $table->decimal('costo_unitario', 10, 2)->default(0);

            $table->timestamps();

            // Un producto solo puede tener un registro por sucursal
            $table->unique(['fk_sucursal', 'fk_producto']);

            // Descomentar cuando los compañeros suban el módulo de sucursal y producto:
            // $table->foreign('fk_sucursal')->references('pk_sucursal')->on('sucursal');
            // $table->foreign('fk_producto')->references('pk_producto')->on('producto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventario');
    }
};
