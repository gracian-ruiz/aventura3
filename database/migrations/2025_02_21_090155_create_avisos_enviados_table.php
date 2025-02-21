<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('avisos_enviados', function (Blueprint $table) {
            $table->id();
            
            // Usuario al que se envió
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Bicicleta asociada (puede ser nulo)
            $table->unsignedBigInteger('bike_id')->nullable();
            $table->foreign('bike_id')->references('id')->on('bikes')->onDelete('cascade');

            // Revisión asociada (puede ser nulo)
            $table->unsignedBigInteger('revision_id')->nullable();
            $table->foreign('revision_id')->references('id')->on('revisions')->onDelete('cascade');

            // Componente asociado (puede ser nulo)
            $table->unsignedBigInteger('componente_id')->nullable();
            $table->foreign('componente_id')->references('id')->on('components')->onDelete('cascade');

            $table->string('telefono'); // Número de WhatsApp al que se envió
            $table->text('mensaje'); // Contenido del mensaje enviado
            $table->timestamp('enviado_en')->useCurrent(); // Fecha y hora del envío

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('avisos_enviados');
    }
};
