<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Categoria;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Crea una categoría con imagen
        $imagePath = public_path('images/mbasicas.jpg');
        $uploadedImage = Cloudinary::upload($imagePath);
        $imageUrl1 = $uploadedImage->getSecurePath();


        // Crea una segunda categoría con imagen
        $imagePath2 = public_path('images/mudanzas_completas.jpg');
        $uploadedImage2 = Cloudinary::upload($imagePath2);
        $imageUrl2 = $uploadedImage2->getSecurePath();

         // Crea una tercera categoría con imagen
         $imagePath3 = public_path('images/mempresariales.jpg');
         $uploadedImage3 = Cloudinary::upload($imagePath3);
         $imageUrl3 = $uploadedImage3->getSecurePath();

        //
        $categorias = [
            ['nombre' => 'Mudanzas Básicas/sencillas',
            'descripcion' => 'Para mudanzas sencillas y economicas, incluye servicios Camión/Transporte y de Personal',
            'image_url' => $imageUrl1
        ],
            ['nombre' => 'Mudanza Completa', 'descripcion' => 'Incluye diferentes servicios de embalaje, Camión/Transporte y de Personal',
            'image_url' => $imageUrl2],
            ['nombre' => 'Servicios Corporativos', 'descripcion' => 'Servicios de mudanzas de empresas, Mudanzas de Oficinas',
            'image_url' => $imageUrl3],
        ];

        Categoria::insert($categorias);
    }
}
