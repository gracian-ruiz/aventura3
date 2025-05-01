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
        Schema::create('alquiler_material', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alquiler_id');
            $table->foreignId('material_id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->decimal('precio_unitario', 8, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2);
            $table->enum('estado', ['finalizado', 'activo','perdido','cancelado'])->default('activo');
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alquiler_material');
    }
};
