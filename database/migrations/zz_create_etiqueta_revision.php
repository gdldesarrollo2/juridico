<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
 public function up(): void {
    Schema::create('etiqueta_revision', function (Blueprint $table) {
      $table->unsignedBigInteger('revision_id');
      $table->unsignedBigInteger('etiqueta_id');
      $table->primary(['revision_id','etiqueta_id']);
      $table->foreign('revision_id')->references('id')->on('revisiones')->cascadeOnDelete();
      $table->foreign('etiqueta_id')->references('id')->on('etiquetas')->cascadeOnDelete();
    });
  }
  public function down(): void {
    Schema::dropIfExists('etiqueta_revision');
  }
};
