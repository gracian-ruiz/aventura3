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
        Schema::table('components', function (Blueprint $table) {
            $table->integer('fecha_preaviso')->default(0)->change();
        });
    }
    
    public function down()
    {
        Schema::table('components', function (Blueprint $table) {
            $table->integer('fecha_preaviso')->nullable()->change();
        });
    }
    
};
