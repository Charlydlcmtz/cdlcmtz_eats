<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;
    protected $table = 'menu';

    public function empresa(){
        return $this->belongsTo('App\Models\Empresa', 'id_empresa');
    }

    //Relacion de Uno a Uno
    public function tipo_menu(){
        return $this->belongsTo('App\Models\TipoMenu', 'id_tipo_menu');
    }

    //Relacion de Uno a Uno
    public function pedidos(){
        return $this->belongsToMany(Pedido::class, 'pedido_menu')->withPivot('cantidad')->withTimestamps();
    }
}
