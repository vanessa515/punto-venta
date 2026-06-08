<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolesPermisos extends Model
{
    protected $table = "roles_permisos";
    protected $primaryKey = "id_rol_permiso";
    protected $fillable = ['fk_rol', 'fk_permiso'];
    
    
}

