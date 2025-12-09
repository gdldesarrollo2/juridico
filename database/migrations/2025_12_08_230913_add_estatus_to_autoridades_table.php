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
    Schema::table('autoridades', function (Blueprint $table) {
        $table->enum('estatus', ['activo', 'inactivo'])
              ->default('activo')
              ->after('nombre');
    });
}

public function down()
{
    Schema::table('autoridades', function (Blueprint $table) {
        $table->dropColumn('estatus');
    });
}

};
