<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';

    protected $fillable = [
        'fk_categoria',
        'fk_marca',
        'nombre',
        'descripcion',
        'unidad_medida',
        'maneja_variantes',
        'aplica_iva',
        'imagen_principal',
        'estatus',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'fk_categoria', 'id_categoria');
    }

    public function marca()
    {
        return $this->belongsTo(Marca::class, 'fk_marca', 'id_marca');
    }

    // Todas las variantes vendibles de este producto
    public function variantes()
    {
        return $this->hasMany(ProductoVariante::class, 'fk_producto', 'id_producto');
    }
}