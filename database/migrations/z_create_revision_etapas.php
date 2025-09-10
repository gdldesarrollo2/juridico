<?php
// xxxx_xx_xx_xxxxxx_create_revision_etapas_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('revision_etapas', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('revision_id');
      $table->unsignedBigInteger('abogado_id')->nullable(); // responsable
      $table->unsignedBigInteger('usuario_id')->nullable(); // quién capturó

      $table->unsignedInteger('orden')->default(1);         // 1., 2., 3. …
      $table->string('nombre');                              // “Atención de primer requerimiento”
      $table->date('fecha_inicio')->nullable();
      $table->unsignedInteger('dias_vencimiento')->nullable();
      $table->date('vence')->nullable();                     // calculado opcional

      $table->text('comentarios')->nullable();
      $table->enum('estatus', ['pendiente','atendido','en_proceso','cerrado'])
            ->default('pendiente');

      $table->timestamps();

      $table->foreign('revision_id')->references('id')->on('revisiones')->cascadeOnDelete();
      $table->foreign('abogado_id')->references('id')->on('abogados')->nullOnDelete();
      $table->foreign('usuario_id')->references('id')->on('users')->nullOnDelete();
    });
  }
  public function down(): void {
    Schema::dropIfExists('revision_etapas');
  }
};
