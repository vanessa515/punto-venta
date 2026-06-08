<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companias extends Model
{
    protected $table = "companias";
    protected $primaryKey = "id_compania";
    protected $fillable = ['nombre','rfc','email','telefono','direccion','logo'];
    
    
}

