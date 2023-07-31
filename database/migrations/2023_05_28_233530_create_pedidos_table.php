<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->text('partida');
            $table->text('destino');
            $table->string('m_pago');
            $table->decimal('iva', 8, 2);
            $table->decimal('subtotal', 8, 2);
            $table->decimal('p_total', 8, 2);
            $table->string('estado');
            $table->text('observacionCli'); //detalle del pedido
            $table->text('observacionAdmin'); //en caso de pedido rechazado
            $table->text('comentarioAdmin'); //respuesta a comentario cli
            $table->text('comentarioCli'); //despues de completar pedido
            $table->integer('calificacion');
            $table->dateTime('fecha_hora');

            $table->timestamps();
        });

        Schema::create('pedido_servicio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('servicio_id');
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('servicio_id')->references('id')->on('servicios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pedido_servicio');
        Schema::dropIfExists('pedidos');
    }
};
