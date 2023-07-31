<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Servicio;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $imagePath = public_path('images/mbasicas.jpg');
        $uploadedImage = Cloudinary::upload($imagePath);
        $imageUrl1 = $uploadedImage->getSecurePath();

        //
        $servicios = [
            ['nombre' => 'Transporte liviano',
            'vehiculo'=>'Toyota Hiace',
            'descripcion' => 'Furgoneta de tamaño mediano con capacidad de 10 m³',
            'precio_h' => 24.99,
            'image' => 'liviano1.jpg'],

            ['nombre' => 'Transporte liviano 2',
            'vehiculo'=>'Mercedes-Benz Sprinter',
            'descripcion' => 'Furgoneta de tamaño mediano con capacidad de 12 m³',
            'precio_h' => 29.99,
            'image' => 'liviano2.jpg'],

            ['nombre' => 'Transporte estandar',
            'vehiculo'=>'CIsuzu NPR',
            'descripcion' => 'Camión liviano con capacidad de carga de 4 toneladas',
            'precio_h' => 34.99,
            'image' => 'estandar.jpg'],

            ['nombre' => 'Transporte pesado',
            'vehiculo'=>'Scania R730',
            'descripcion' => 'Camión de alto rendimiento con capacidad de carga de 25 toneladas',
            'precio_h' => 50,
            'image' => 'pesado.jpg'],

            ['nombre' => 'Carga y descarga',
            'vehiculo'=>'-',
            'descripcion' => 'Incluye 2 empleado para la carga y descarga de bienes',
            'precio_h' => 9.99,
            'image' => 'carga_descarga1.jpg'],

            ['nombre' => 'Carga, descarga y embalaje',
            'vehiculo'=>'-',
            'descripcion' => 'Incluye embalaje de pertenencias delicadas y muebles(Cinta, Plástico Strech, Mantas) y 2 empleado para carga, descarga y embalaje',
            'precio_h' => 14.99,
            'image' => 'carga_descarga.jpg'],

            ['nombre' => 'Carga, descarga y embalaje total',
            'vehiculo'=>'-',
            'descripcion' => 'Incluye embalaje de todas las pertenencias(Cinta, Plástico Strech, Mantas, Cajas de Cartón, Papel para envolver Vajilla, Cristalería y adornos delicados.) y 3 empleado para carga, descarga y embalaje',
            'precio_h' => 29.99,
            'image' => 'embalajetotal.jpg']

        ];

        foreach ($servicios as $servicio) {
            $imagePath = public_path('images/' . $servicio['image']);
            $uploadedImage = Cloudinary::upload($imagePath);
            $imageUrl = $uploadedImage->getSecurePath();

            Servicio::create([
                'nombre' => $servicio['nombre'],
                'vehiculo' => $servicio['vehiculo'],
                'descripcion' => $servicio['descripcion'],
                'precio_h' => $servicio['precio_h'],
                'image_url' => $imageUrl,
            ]);
        }
        $this->command->info('Seeding de Servicios completado.');
    }
}
