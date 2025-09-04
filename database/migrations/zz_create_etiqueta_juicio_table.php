<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('etiqueta_juicio', function (Blueprint $table) {
            $table->unsignedBigInteger('juicio_id');
            $table->unsignedBigInteger('etiqueta_id');

            $table->foreign('juicio_id')
                  ->references('id')->on('juicios')
                  ->onDelete('cascade');

            $table->foreign('etiqueta_id')
                  ->references('id')->on('etiquetas')
                  ->onDelete('cascade');

            $table->primary(['juicio_id', 'etiqueta_id']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('etiqueta_juicio');
    }
};
