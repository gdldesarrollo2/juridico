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
            $table->json('periodos')->nullable()->after('revision'); // {"2021":[1,2,...], "2022":[...]}
        });
    }

    public function down(): void
    {
        Schema::table('revisiones', function (Blueprint $table) {
            $table->dropColumn('periodos');
        });
    }
};
