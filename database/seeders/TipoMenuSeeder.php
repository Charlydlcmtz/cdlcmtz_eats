<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\TipoMenu;

class TipoMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipo_menu = TipoMenu::create([
            'nombre_menu' => 'Menu Libre',
            'descripcion_menu' => 'este menu es para aquellos que no estan designados a una empresa.',
        ]);
    }
}
