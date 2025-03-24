<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $role = Role::create([
            'nombre' => 'Admin_Role',
            'descripcion' => 'rol con todos los permisos.',
        ]);

        $role = Role::create([
            'nombre' => 'User_Role',
            'descripcion' => 'rol común.',
        ]);

        $role = Role::create([
            'nombre' => 'Chef_Role',
            'descripcion' => 'rol con todos los permisos.',
        ]);

        $role = Role::create([
            'nombre' => 'Company_Role',
            'descripcion' => 'rol con todos los permisos.',
        ]);

        $role = Role::create([
            'nombre' => 'Delivery_Role',
            'descripcion' => 'rol con todos los permisos.',
        ]);
    }
}
