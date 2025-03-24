<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UsuariosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $password = "Jacobyshidix1";

        $user = new User([
            'nombre' => 'Carlos',
            'username' => 'Charlydlcmtz',
            'apellido_p' => 'De La Cruz',
            'apellido_m' => 'Martínez',
            'telefono' => '8112881850',
            'id_role' => 1,
            'correo' => 'carlos.cmtz@hotmail.com',
            'password' => bcrypt($password),
            'created_at' => '2024-05-19',
            'updated_at' => '2024-05-19'
        ]);
        $user->saveOrFail();
    }
}
