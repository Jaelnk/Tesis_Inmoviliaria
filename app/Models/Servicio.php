<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{

    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre', 'vehiculo', 'descripcion', 'precio_h', 'categoria_id'
    ];



    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'servicio_categoria', 'servicio_id', 'categoria_id');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_servicio', 'servicio_id', 'pedido_id');
    }
}


    /* $table->id();
    $table->string('nombre');
    $table->text('vehiculo');
    $table->text('descripcion');
    $table->decimal('precio_h', 8, 2);
    $table->unsignedBigInteger('categoria_id');
    $table->timestamps();

    $table->foreign('categoria_id') */

    // Relación con la categoría
/*     public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    } */
