<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('revisions', function (Blueprint $table) {
            // Añade la columna boolean con default false (en MySQL será TINYINT(1))
            $table->boolean('enviado')->default(false)->after('proxima_revision');
        });
    }

    public function down(): void
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->dropColumn('enviado');
        });
    }
};
