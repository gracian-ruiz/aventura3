<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cambiar el enum 'tipo' para añadir nuevos valores
        DB::statement("ALTER TABLE materials MODIFY COLUMN tipo ENUM('mtb','electrica','mtb26','mtb29','mtb29doble','electricapaseo','electricadoble','electricarigida','carretera','paseo','niños','casco','material','bidones')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE materials MODIFY COLUMN tipo ENUM('mtb','electrica','carretera','paseo','niños','casco','material','bidones')");
    }
};
