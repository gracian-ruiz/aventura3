<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appointment_component', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id');
            $table->foreignId('componente_id');
            $table->string('texto'); // Descripción del ítem
            $table->integer('total_precio'); // Precio total del componente en la cita
            $table->integer('horas_trabajo'); // Horas de trabajo estimadas para el componente
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointment_component');
    }
};
