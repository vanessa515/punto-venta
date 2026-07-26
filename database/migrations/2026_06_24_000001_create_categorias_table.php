<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Clasificación de productos. Admite subcategorías opcionales
     * mediante fk_categoria_padre (NULL = categoría raíz).
     */
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id('id_categoria');
            $table->foreignId('fk_categoria_padre')
                  ->nullable()
                  ->references('id_categoria')
                  ->on('categorias');
            $table->string('nombre', 100);
            $table->string('descripcion', 255)->nullable();
            $table->string('imagen', 250)->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};