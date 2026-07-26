<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductoVariante extends Model
{
    protected $table = 'producto_variantes';
    protected $primaryKey = 'id_variante';

    protected $fillable = [
        'fk_producto',
        'sku',
        'talla',
        'color',
        'precio_compra',
        'precio_venta',
        'imagen',
        'estatus',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'fk_producto', 'id_producto');
    }

    // Códigos de barras asociados a esta variante
    public function codigosBarras()
    {
        return $this->hasMany(CodigoBarra::class, 'fk_variante', 'id_variante');
    }

    // NOTA PARA EL EQUIPO:
    // El módulo de Inventario (stock por sucursal) y el módulo
    // de Ventas (detalle de venta) deben apuntar a este modelo
    // via fk_variante -> id_variante, NUNCA a Producto directamente.
}