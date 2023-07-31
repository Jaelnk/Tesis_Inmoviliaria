<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
        "cedula",
        "celular",
        "fnac",
        "direccion",
        "role_id"
    ];


    protected $hidden = [
        'password',
    ];


    public function roles()
    {
        return $this->belongsTo(Role::class);
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_usuario', 'user_id', 'pedido_id');
    }



}
