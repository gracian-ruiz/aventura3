<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('components', function (Blueprint $table) {
            // Campo de texto largo, opcional. Colócalo donde prefieras.
            $table->text('descripcion')->nullable()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('components', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
    }
};
