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
        Schema::table('alquileres', function (Blueprint $table) {
            $table->boolean('notificacion')->default(false)->after('fallo'); // O donde quieras colocarlo
        });
    }

    public function down()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn('notificacion');
        });
    }

};
