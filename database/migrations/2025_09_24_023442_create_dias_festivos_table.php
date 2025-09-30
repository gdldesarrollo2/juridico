<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void {
        Schema::create('dias_festivos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('autoridad_id');
            $table->unsignedSmallInteger('anio');
            $table->date('fecha');
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();

            $table->unique(['autoridad_id','anio','fecha']); // evita duplicados
            $table->foreign('autoridad_id')->references('id')->on('autoridades')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('dias_festivos');
    }
};
