<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('fecha_preaviso')->default(0);
            $table->integer('fecha_revision')->nullable(); // Se almacena en meses
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('components');
    }
};
