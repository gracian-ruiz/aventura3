<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bike_id')->constrained()->onDelete('cascade');
            $table->foreignId('componente_id')->nullable()->constrained('components')->onDelete('cascade'); // Relación con componentes
            $table->date('fecha_revision');
            $table->text('descripcion');
            $table->date('proxima_revision')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('revisions');
    }
};
