<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Estatus;

class EstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estatus = Estatus::create([
            'nombre' => 'Pedido Ordenado',
            'descripcion' => 'El pedido fue hecho por el cliente o usuario.',
        ]);

        $estatus = Estatus::create([
            'nombre' => 'En proceso',
            'descripcion' => 'El cocinero esta preparndo la comida.',
        ]);

        $estatus = Estatus::create([
            'nombre' => 'Completado',
            'descripcion' => 'El cocinero terminó la elaboración de la comida.',
        ]);

        $estatus = Estatus::create([
            'nombre' => 'Entregado',
            'descripcion' => 'El platillo fue entregado al cliente o usuario.',
        ]);

        $estatus = Estatus::create([
            'nombre' => 'Cancelado',
            'descripcion' => 'El platillo fue cancelado por el cocinero, usuario u cliente.',
        ]);
    }
}
