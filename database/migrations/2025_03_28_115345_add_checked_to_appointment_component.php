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
        Schema::table('appointment_component', function (Blueprint $table) {
            $table->boolean('checked')->default(false)->after('horas_trabajo');
            $table->unsignedBigInteger('usuario_taller_id')->nullable()->after('checked');
        });
    }

    public function down()
    {
        Schema::table('appointment_component', function (Blueprint $table) {
            $table->dropColumn('checked');
        });
    }
};
