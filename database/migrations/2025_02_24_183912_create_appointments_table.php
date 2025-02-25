<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bike_id')->constrained()->onDelete('cascade');
            $table->foreignId('componente_id')->nullable()->constrained('components')->onDelete('set null');
            $table->enum('prioridad', ['normal', 'urgente'])->default('normal');
            $table->enum('estado', ['pendiente', 'en proceso', 'completada'])->default('pendiente');
            $table->integer('tiempo_estimado')->default(0); // Minutos de estimación
            $table->text('descripcion_problema')->nullable(); // Descripción del problema
            $table->text('estimacion_reparacion')->nullable(); // Estimación de reparación
            $table->date('fecha_asignada')->nullable(); // Día asignado
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
