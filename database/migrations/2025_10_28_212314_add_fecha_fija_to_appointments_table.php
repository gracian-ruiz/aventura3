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
    Schema::table('appointments', function (Blueprint $table) {
        $table->boolean('fecha_fija')->default(false)->after('fecha_asignada');
    });
}


    /**
     * Reverse the migrations.
     */
public function down()
{
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropColumn('fecha_fija');
    });
}
};
