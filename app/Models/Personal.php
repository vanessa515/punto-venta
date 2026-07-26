<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personal extends Model
{
    protected $table = "dpersonales";
    protected $primaryKey = "id_dpersonal";
    protected $fillable = ['nombre','cp','direccion','telefono','principal','fk_sucursal'];
    
    
}

