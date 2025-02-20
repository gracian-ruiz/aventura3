<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('components', function (Blueprint $table) {
            $table->id(); // Esto ya es bigIncrements automáticamente
            $table->string('nombre');
            $table->integer('fecha_preaviso');
            $table->timestamps();
        });
    }
    
    
    
    
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('components');
    }
};
