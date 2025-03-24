<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $tables = 'roles';

    public function users(){
        return $this->hasMany(User::class, 'id_role');
    }
    public function empresas(){
        return $this->hasMany(Empresa::class, 'id_role');
    }
}
