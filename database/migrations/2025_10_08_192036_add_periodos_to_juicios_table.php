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
    Schema::table('juicios', function (Blueprint $table) {
        $table->json('periodos')->nullable()->after('estatus');
    });
}

public function down()
{
    Schema::table('juicios', function (Blueprint $table) {
        $table->dropColumn('periodos');
    });
}
};
