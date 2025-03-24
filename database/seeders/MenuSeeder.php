<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menu = Menu::create([
            'platillo' => 'Tacos de Cochinita pibil',
            'descripcion' => 'Cuando las taquizas de guisos son pensadas para celebraciones muy especiales hay que incluir sí o sí la cochinita pibil. Es una receta rendidora y su contraste de sabores ácidos con otros dulces cautiva hasta los paladares más exigentes, por algo fue nombrada el mejor plato tradicional del mundo por Taste Atlas Awards 2021.',
            'costo' => '10',
            'calorias' => '',
            'img_comida' => 'tacos_cochinita.jpg',
            'inicio_fecha_platillo' => null,
            'fin_fecha_platillo' => null,
            'id_tipo_menu' => 1,
        ]);

        $menu = Menu::create([
            'platillo' => 'Tacos de Tinga de pollo',
            'descripcion' => 'La tinga es carne deshebrada, o como se le conoce en otros países, mechada o desmechada; la tinga es uno de los rellenos preferidos para tacos sudados y para taquizas. ¿Quieres darle un giro muy especial? Carameliza o acitrona las cebollas con azúcar y piloncillo para que cuando se mezcle con el chile tenga el sabor agridulce perfecto, más dulce que ácido. Para conseguir ese sabor, debes reducir el tiempo de estancia de los chiles en la salsa y disminuir la cantidad de chile, usando solo uno o dos.',
            'costo' => '10',
            'calorias' => '',
            'img_comida' => 'tacos_tinga.jpg',
            'inicio_fecha_platillo' => null,
            'fin_fecha_platillo' => null,
            'id_tipo_menu' => 1,
        ]);

        $menu = Menu::create([
            'platillo' => 'Tacos de Pollo deshebrado con mole',
            'descripcion' => 'Aunque el mole es un platillo prehispánico de lujo, siempre está presente en las grandes celebraciones mexicanas, también forma parte de esos antojitos callejeros que tanto nos gustan. ¿Quién no ha comido mole en enchiladas, hojaldras o tacos de canasta? Por eso lo hemos incluido en nuestra degustación virtual para que lo tengas en cuenta cuando prepares guisos para taquizas, tacos sudados o simplemente cuando quieras darte un gustazo.',
            'costo' => '10',
            'calorias' => '',
            'img_comida' => 'tacos_mole.jpg',
            'inicio_fecha_platillo' => null,
            'fin_fecha_platillo' => null,
            'id_tipo_menu' => 1,
        ]);

        $menu = Menu::create([
            'platillo' => 'Tacos de Chicharrón en salsa verde',
            'descripcion' => 'Los tacos de canasta son un clásico de la comida callejera mexicana, llamados así porque los transportan en una canasta. Se dice que nacen en la década de los años 50, y entre sus primeros rellenos, figura el guiso de chicharrón. Nosotros decidimos traerte este guiso clásico en una variante deliciosa, muy popular entre las recetas del mes patrio, pues tiene uno de los colores de la bandera. ¡Un guiso que tampoco puede faltar en una taquiza!.',
            'costo' => '10',
            'calorias' => '',
            'img_comida' => 'tacos_chicharron.jpg',
            'inicio_fecha_platillo' => null,
            'fin_fecha_platillo' => null,
            'id_tipo_menu' => 1,
        ]);

        $menu = Menu::create([
            'platillo' => 'Tacos de Alambre',
            'descripcion' => 'El alambre es uno de los platillos típicos de México más populares, se prepara con carne, tocino, pimiento morrón y cebolla, en un inicio se ensartaban los ingredientes en una varilla de alambre y de ahí viene su nombre.',
            'costo' => '10',
            'calorias' => '',
            'img_comida' => 'tacos_alambre.jpg',
            'inicio_fecha_platillo' => null,
            'fin_fecha_platillo' => null,
            'id_tipo_menu' => 1,
        ]);
    }
}
