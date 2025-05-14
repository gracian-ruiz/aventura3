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
            $table->integer('reserva')->nullable();
            $table->integer('reserva_precio')->nullable()->after('reserva');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('alquileres', function (Blueprint $table) {
            $table->dropColumn(['reserva', 'reserva_precio']);
        });
    }
};
