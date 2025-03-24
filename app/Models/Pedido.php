<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $table = 'pedidos';

    public function menu(){
        return $this->belongsToMany(Menu::class, 'pedido_menu')->withPivot('cantidad')->withTimestamps();
    }

    public function estatus(){
        return $this->belongsTo('App\Models\Estatus', 'id_estatus');
    }

    //Relacion de Uno a Uno
    public function empresas(){
        return $this->belongsTo('App\Models\Empresa', 'id_empresa');
    }
    public function tipo_menu(){
        return $this->belongsTo('App\Models\TipoMenu', 'id_tipo_menu');
    }

    //Relacion de Uno a Uno
    public function usuario(){
        return $this->belongsTo('App\Models\User', 'id_user');
    }

}
