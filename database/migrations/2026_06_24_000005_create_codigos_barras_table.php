<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Códigos de barras por variante. Una variante puede tener
     * varios (EAN de fábrica, QR interno, etc). es_principal
     * marca cuál se muestra/imprime por defecto.
     */
    public function up(): void
    {
        Schema::create('codigos_barras', function (Blueprint $table) {
            $table->id('id_codigo');
            $table->foreignId('fk_variante')
                  ->references('id_variante')
                  ->on('producto_variantes');
            $table->string('codigo', 100)->unique();
            $table->string('tipo', 20)->default('EAN13');
            $table->boolean('es_principal')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_barras');
    }
};