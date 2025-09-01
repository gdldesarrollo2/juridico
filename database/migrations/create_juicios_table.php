<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   // database/migrations/xxxx_create_juicios_table.php
public function up(): void {
      Schema::create('juicios', function (Blueprint $table) {
    $table->bigIncrements('id');

    // columnas FK como BIGINT UNSIGNED
    $table->unsignedBigInteger('cliente_id');
    $table->string('nombre');
    $table->unsignedBigInteger('autoridad_id')->nullable();
    $table->unsignedBigInteger('abogado_id')->nullable();

    // resto de campos
    $table->enum('tipo', ['nulidad','revocacion']);
    $table->date('fecha_inicio')->nullable();
    $table->decimal('monto', 14, 2)->nullable();
    $table->text('observaciones_monto')->nullable();
    $table->string('resolucion_impugnada')->nullable();
    $table->string('garantia')->nullable();
    $table->string('numero_juicio')->nullable();
    $table->string('numero_expediente')->nullable();
    $table->enum('estatus', ['juicio','autorizado','en_proceso','concluido'])->default('juicio');

    $table->timestamps();
});

// agrega las claves foráneas en un paso aparte (ya existen las tablas)
Schema::table('juicios', function (Blueprint $table) {
    $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
    $table->foreign('autoridad_id')->references('id')->on('autoridades')->nullOnDelete();
    $table->foreign('abogado_id')->references('id')->on('abogados')->nullOnDelete();
});

    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('juicios');
    }
};
