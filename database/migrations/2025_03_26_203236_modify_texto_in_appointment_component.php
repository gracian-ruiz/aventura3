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
            $table->string('texto')->nullable()->default('')->change();
        });
    }
    
    public function down()
    {
        Schema::table('appointment_component', function (Blueprint $table) {
            $table->string('texto')->nullable(false)->change();
        });
    }
    
};
