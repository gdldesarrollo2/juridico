<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
      Schema::create('etapas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('juicio_id');
            $table->foreign('juicio_id')->references('id')->on('juicios')->cascadeOnDelete();

            $table->unsignedBigInteger('etiqueta_id')->nullable();
            $table->foreign('etiqueta_id')->references('id')->on('etiquetas')->nullOnDelete();

            $table->string('etapa');

            $table->unsignedBigInteger('usuario_id')->nullable();
            $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();

            $table->string('rol')->nullable();
            $table->text('comentarios')->nullable();

            // ✅ NUEVO: fecha de inicio de la etapa
            $table->date('fecha_inicio')->nullable();

            $table->unsignedInteger('dias_vencimiento')->default(0);
            $table->date('fecha_vencimiento')->nullable();

            $table->enum('estatus', ['en_tramite','en_juicio','concluido','cancelado'])
                ->default('en_tramite');

            $table->string('archivo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('etapas');
    }
};