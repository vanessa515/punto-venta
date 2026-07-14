<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    protected $table      = 'transferencia';
    protected $primaryKey = 'pk_transferencia';

    protected $fillable = [
        'fk_sucursal_origen',
        'fk_sucursal_destino',
        'fk_usuario_solicita',
        'fk_usuario_recibe',
        'estado',
        'notas',
    ];

    // ─── Relaciones ──────────────────────────────────────────────

    public function detalles()
    {
        return $this->hasMany(DetalleTransferencia::class, 'fk_transferencia', 'pk_transferencia');
    }

    public function usuarioSolicita()
    {
        return $this->belongsTo(User::class, 'fk_usuario_solicita', 'id');
    }

    public function usuarioRecibe()
    {
        return $this->belongsTo(User::class, 'fk_usuario_recibe', 'id');
    }
}
