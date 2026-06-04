<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    protected $table = "roles";
    protected $primaryKey = "id_rol";
    protected $fillable = ['nombre'];

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'roles_permisos', 'fk_rol', 'fk_permiso');
    }
    
}

