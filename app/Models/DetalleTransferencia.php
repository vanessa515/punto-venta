<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleTransferencia extends Model
{
    protected $table      = 'detalle_transferencia';
    protected $primaryKey = 'pk_detalle';

    protected $fillable = [
        'fk_transferencia',
        'fk_producto',
        'cantidad',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function transferencia()
    {
        return $this->belongsTo(Transferencia::class, 'fk_transferencia', 'pk_transferencia');
    }
}
