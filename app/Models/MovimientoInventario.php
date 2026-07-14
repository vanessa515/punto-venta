<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table      = 'movimiento_inventario';
    protected $primaryKey = 'pk_movimiento';

    protected $fillable = [
        'fk_sucursal',
        'fk_producto',
        'fk_usuario',
        'fk_transferencia',
        'tipo_movimiento',
        'cantidad',
        'costo_unitario',
        'referencia',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function usuario()
    {
        return $this->belongsTo(User::class, 'fk_usuario', 'id');
    }

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class, 'fk_transferencia', 'pk_transferencia');
    }
}
