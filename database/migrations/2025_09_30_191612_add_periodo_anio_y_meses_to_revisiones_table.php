<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('revisiones', function (Blueprint $table) {
            $table->smallInteger('periodo_anio')->nullable()->after('revision');
            $table->json('periodo_meses')->nullable()->after('periodo_anio');
            // Opcional: si ya no usarás estas columnas, déjalas nullable o bórralas luego
            // $table->dropColumn(['periodo_desde','periodo_hasta']);
        });
    }
    public function down(): void {
        Schema::table('revisiones', function (Blueprint $table) {
            $table->dropColumn(['periodo_anio','periodo_meses']);
        });
    }
};
