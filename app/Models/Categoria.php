<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $fillable = ['nombre','descripcion', 'image_url'];

/*     public function servicios()
    {
        return $this->hasMany(Servicio::class);
    } */

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_categoria', 'categoria_id', 'servicio_id');
    }

}
