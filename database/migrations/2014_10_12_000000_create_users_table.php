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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('cedula');
            $table->string('celular');
            $table->string('fnac');
            $table->string('direccion');
            $table->unsignedBigInteger('role_id');
            $table->timestamps(); //fechas de creacion

            $table->foreign('role_id')
            ->references('id')
            ->on('roles')
            ->onDelete('cascade');
        });
    }

    public function before()
    {
        Schema::dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
