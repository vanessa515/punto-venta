<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodigoBarra extends Model
{
    protected $table = 'codigos_barras';
    protected $primaryKey = 'id_codigo';
    public $timestamps = false;

    protected $fillable = [
        'fk_variante',
        'codigo',
        'tipo',
        'es_principal',
    ];

    public function variante()
    {
        return $this->belongsTo(ProductoVariante::class, 'fk_variante', 'id_variante');
    }
}