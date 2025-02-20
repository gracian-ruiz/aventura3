<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bike_id');
            $table->unsignedBigInteger('componente_id')->nullable(); // 🔹 Se agrega aquí directamente
            $table->date('fecha_revision');
            $table->text('descripcion');
            $table->date('proxima_revision')->nullable();
            $table->timestamps();
    
            $table->foreign('bike_id')->references('id')->on('bikes')->onDelete('cascade');
            $table->foreign('componente_id')->references('id')->on('components')->onDelete('cascade'); // 🔹 Clave foránea
        });
    }
    
    
    
    

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};

