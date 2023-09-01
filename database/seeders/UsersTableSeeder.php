<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Añade usuarios
        $users = [
            [
                'name' => '---',
                'email' => 'adminEmail@example.com',
                'password' => Hash::make('administrador'),
                'cedula' => '---',
                'celular' => '---',
                'fnac' => '---',
                'direccion' => '---',
                'role_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '---',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('ClientePass456'),
                'cedula' => '---',
                'celular' => '---',
                'fnac' => '---',
                'direccion' => '---',
                'role_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => '---',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('EmpleadoPass123'),
                'cedula' => '---',
                'celular' => '---',
                'fnac' => '---',
                'direccion' => '---',
                'role_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Inserta los usuarios en la base de datos
        DB::table('users')->insert($users);
    }
}
