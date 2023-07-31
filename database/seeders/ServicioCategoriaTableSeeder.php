<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ServicioCategoria;

class ServicioCategoriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        ServicioCategoria::create(['servicio_id' => 1, 'categoria_id' => 1]);
        ServicioCategoria::create(['servicio_id' => 1, 'categoria_id' => 2]);

        ServicioCategoria::create(['servicio_id' => 2, 'categoria_id' => 1]);
        ServicioCategoria::create(['servicio_id' => 2, 'categoria_id' => 2]);

        ServicioCategoria::create(['servicio_id' => 3, 'categoria_id' => 1]);
        ServicioCategoria::create(['servicio_id' => 3, 'categoria_id' => 2]);
        ServicioCategoria::create(['servicio_id' => 3, 'categoria_id' => 3]);

        ServicioCategoria::create(['servicio_id' => 4, 'categoria_id' => 3]);

        ServicioCategoria::create(['servicio_id' => 5, 'categoria_id' => 1]);
        ServicioCategoria::create(['servicio_id' => 5, 'categoria_id' => 2]);
        ServicioCategoria::create(['servicio_id' => 5, 'categoria_id' => 3]);

        ServicioCategoria::create(['servicio_id' => 6, 'categoria_id' => 2]);
        ServicioCategoria::create(['servicio_id' => 6, 'categoria_id' => 3]);

        ServicioCategoria::create(['servicio_id' => 7, 'categoria_id' => 2]);
        ServicioCategoria::create(['servicio_id' => 7, 'categoria_id' => 3]);
    }
}
