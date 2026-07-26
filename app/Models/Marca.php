<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';

    protected $fillable = [
        'nombre',
        'logo',
        'estatus',
    ];

    // Productos que pertenecen a esta marca
    public function productos()
    {
        return $this->hasMany(Producto::class, 'fk_marca', 'id_marca');
    }
}