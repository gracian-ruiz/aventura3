<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bike_id');
            $table->foreignId('user_id');
            $table->integer('presupuesto_id')->nullable();
            $table->enum('prioridad', ['normal', 'urgente'])->default('normal');
            $table->enum('estado', ['vacia','presupuesto','denegado','pendiente', 'en proceso', 'completada', 'reparacion',])->default('presupuesto');
            $table->integer('tiempo_estimado')->default(0); // Minutos de estimación
            $table->text('descripcion_problema')->nullable(); // Descripción del problema
            $table->text('estimacion_reparacion')->nullable(0); // Estimación de reparación
            $table->date('fecha_asignada')->nullable(); // Día asignado
            $table->unsignedBigInteger('usuario_taller_id')->nullable();
            $table->boolean('checked')->default(false);
            $table->string('token_presupuesto')->unique()->nullable();
            $table->boolean('presupuesto_enviado')->default(false);
            $table->boolean('reparacion_enviado')->default(false);
            $table->integer('horas_total')->default(0);
            $table->integer('precio_total')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
