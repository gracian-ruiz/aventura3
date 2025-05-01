<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alquileres', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('total_dias')->nullable(); // puedes calcularlo por lógica
            $table->decimal('total_precio', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->nullable();
            $table->enum('estado', ['activo', 'finalizado', 'cancelado','reservado'])->default('activo');
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alquilers');
    }
};
