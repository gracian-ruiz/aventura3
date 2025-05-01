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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['mtb','electrica','carretera','paseo','niños', 'casco', 'material', 'bidones']);
            $table->string('nombre'); // puede ser código también
            $table->string('talla')->nullable(); // puede ser S, M, L, 54, 56, etc.
            $table->integer('stock')->default(1); // cantidad total disponible
            $table->integer('stock_disponible')->default(1);
            $table->enum('estado', ['disponible', 'mantenimiento'])->default('disponible');
            $table->text('descripcion')->nullable();
            $table->text('categoria')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('precio_dia', 8, 2);
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
