<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('revisiones', function (Blueprint $table) {
        $table->string('no_juicio')->nullable()->after('id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('revisiones', function (Blueprint $table) {
        $table->dropColumn('no_juicio');
    });
    }
};
