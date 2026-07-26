<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';

    protected $fillable = [
        'fk_categoria_padre',
        'nombre',
        'descripcion',
        'imagen',
        'estatus',
    ];

    // Categoría padre (si esta es una subcategoría)
    public function padre()
    {
        return $this->belongsTo(Categoria::class, 'fk_categoria_padre', 'id_categoria');
    }

    // Subcategorías hijas de esta categoría
    public function subcategorias()
    {
        return $this->hasMany(Categoria::class, 'fk_categoria_padre', 'id_categoria');
    }

    // Productos que pertenecen a esta categoría
    public function productos()
    {
        return $this->hasMany(Producto::class, 'fk_categoria', 'id_categoria');
    }
}