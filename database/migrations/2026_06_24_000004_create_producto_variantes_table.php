<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unidad real que se vende: SKU y precio propios.
     * Todo producto tiene >=1 variante. Si es producto simple,
     * talla y color quedan en NULL.
     */
    public function up(): void
    {
        Schema::create('producto_variantes', function (Blueprint $table) {
            $table->id('id_variante');
            $table->foreignId('fk_producto')
                  ->references('id_producto')
                  ->on('productos');
            $table->string('sku', 60)->unique();
            $table->string('talla', 20)->nullable();
            $table->string('color', 40)->nullable();
            $table->decimal('precio_compra', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);
            $table->string('imagen', 250)->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_variantes');
    }
};