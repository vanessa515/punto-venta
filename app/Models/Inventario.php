<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    protected $table      = 'inventario';
    protected $primaryKey = 'pk_inventario';

    protected $fillable = [
        'fk_sucursal',
        'fk_producto',
        'cantidad',
        'stock_minimo',
        'stock_maximo',
        'costo_unitario',
    ];

    // Incluye bajo_stock automáticamente en el JSON que recibe Vue
    protected $appends = ['bajo_stock'];

    // ─── Accesores útiles ────────────────────────────────────────

    /**
     * Indica si el stock actual está en o por debajo del mínimo.
     */
    public function getBajoStockAttribute(): bool
    {
        return $this->cantidad <= $this->stock_minimo;
    }

    // ─── Relaciones ──────────────────────────────────────────────

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class, 'fk_producto', 'fk_producto')
                    ->where('fk_sucursal', $this->fk_sucursal);
    }
}
