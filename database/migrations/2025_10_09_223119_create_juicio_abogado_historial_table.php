<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void {
        Schema::create('juicio_abogado_historial', function (Blueprint $t) {
            $t->id();
            $t->foreignId('juicio_id')->constrained()->cascadeOnDelete();
            $t->foreignId('abogado_id')->constrained()->restrictOnDelete();
            $t->timestamp('asignado_desde')->nullable();
            $t->timestamp('asignado_hasta')->nullable(); // null = vigente
            $t->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('motivo')->nullable(); // opcional
            $t->timestamps();

            $t->index(['juicio_id','asignado_hasta']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('juicio_abogado_historial');
    }
};
