<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sucursales extends Model
{
    protected $table = "sucursales";
    protected $primaryKey = "id_sucursal";
    protected $fillable = ['nombre','rfc','email','telefono','direccion','logo'];
    
    
}

