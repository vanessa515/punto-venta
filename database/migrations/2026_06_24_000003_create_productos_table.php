<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Producto comercial general. El precio y el stock NO viven
     * aquí, sino en producto_variantes. unidad_medida se queda
     * como texto libre (pieza, kg, litro) porque casi nunca se
     * filtra ni reporta por separado.
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->foreignId('fk_categoria')
                  ->nullable()
                  ->references('id_categoria')
                  ->on('categorias');
            $table->foreignId('fk_marca')
                  ->nullable()
                  ->references('id_marca')
                  ->on('marcas');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('unidad_medida', 20)->default('pieza');
            $table->boolean('maneja_variantes')->default(false)
                  ->comment('0=producto simple, 1=tiene variantes (talla/color)');
            $table->boolean('aplica_iva')->default(true);
            $table->string('imagen_principal', 250)->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};