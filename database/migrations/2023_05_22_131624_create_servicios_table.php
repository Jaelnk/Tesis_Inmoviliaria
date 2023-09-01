<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('vehiculo');
            $table->text('descripcion');
            $table->decimal('precio_h', 8, 2);
            $table->string('image_url')->nullable();
            //$table->unsignedBigInteger('categoria_id');
            $table->timestamps();

            //$table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicios');
    }
};
