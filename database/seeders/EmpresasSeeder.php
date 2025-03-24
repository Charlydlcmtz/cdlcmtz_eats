<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Empresa;

class EmpresasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menu = Empresa::create([
            'nombre' => 'BIMBO',
            'rfc' => 'DWRGWEHRT134234RWRGSEHS2',
            'telefono' => '5581128345',
            'correo' => 'prueba_bimbo@bimbo.com',
            'icon' => 'Bimbo.jpg',
            'colors' => '#0033A0',
            'id_role' => 4,
            'password' => bcrypt('Bimbo123.'),
        ]);

        $menu = Empresa::create([
            'nombre' => 'Ternium',
            'rfc' => 'DWRGWEHRJHUYGJNLJH13324',
            'telefono' => '5581127366',
            'correo' => 'prueba_ternium@ternium.com',
            'icon' => 'Ternium.jpg',
            'colors' => '#f30',
            'id_role' => 4,
            'password' => bcrypt('Bimbo123.'),
        ]);
    }
}
