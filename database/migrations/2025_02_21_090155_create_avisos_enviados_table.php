<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('avisos_enviados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id'); // Relación con usuario
            $table->foreignId('bike_id');
            $table->foreignId('revision_id');
            $table->foreignId('componente_id');
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
