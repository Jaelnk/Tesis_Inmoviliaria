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
        //Añade usuario administrador
        DB::table('users')->insert([
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
            'email' => 'cliEmail@example.com',
            'password' => Hash::make('12345'),
            'cedula' => '---',
            'celular' => '---',
            'fnac' => '---',
            'direccion' => '---',
            'role_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
