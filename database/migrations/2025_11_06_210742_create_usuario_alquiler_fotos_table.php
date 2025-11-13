<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Ejecuta la migración.
     */
    public function up(): void
    {
        Schema::create('usuario_alquiler_fotos', function (Blueprint $table) {
            $table->id();

            // 🔗 Relación con el alquiler
            $table->foreignId('alquiler_id')
                ->constrained('alquileres')
                ->onDelete('cascade'); // Si se borra el alquiler, se borran las fotos asociadas

            // 📸 Ruta física de la imagen
            $table->string('ruta', 255);

            // 🏷️ Tipo de imagen (por ejemplo: dni_frontal, dni_trasero, contrato, etc.)
            $table->string('tipo', 50)->nullable();

            $table->timestamps();

            // 🔍 Índice para mejorar búsquedas
            $table->index('alquiler_id', 'idx_foto_alquiler');
        });
    }

    /**
     * Revierte la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario_alquiler_fotos');
    }
};
