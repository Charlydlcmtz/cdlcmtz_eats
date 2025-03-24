<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $hidden = [
        'password',
        'id_role',
    ];

    public function role(){
        return $this->belongsTo('App\Models\Role', 'id_role');
    }
}
