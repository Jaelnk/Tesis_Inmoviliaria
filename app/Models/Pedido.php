<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $fillable = [
        'partida','destino','m_pago', 'p_total', 'estado', 'observaciones', 'fecha_hora'
    ];

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'pedido_servicio', 'pedido_id', 'servicio_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'pedido_usuario', 'pedido_id', 'user_id');
    }
}
