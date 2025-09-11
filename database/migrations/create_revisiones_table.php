<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_revisiones_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('revisiones', function (Blueprint $table) {
    $table->id();

    // TIPOS DE COLUMNA CORRECTOS
    $table->unsignedInteger('idempresa');                  // int unsigned (coincide con empresas.idempresa)
    $table->unsignedBigInteger('autoridad_id')->nullable(); // bigint unsigned (coincide con autoridades.id)
    $table->unsignedBigInteger('usuario_id')->nullable();   // bigint unsigned (coincide con users.id)

    // Flags de tipo
    $table->boolean('rev_gabinete')->default(false);
    $table->boolean('rev_domiciliaria')->default(false);
    $table->boolean('rev_electronica')->default(false);
    $table->boolean('rev_secuencial')->default(false);

    $table->string('revision')->nullable();
    $table->date('periodo_desde')->nullable();
    $table->date('periodo_hasta')->nullable();

    $table->string('objeto')->nullable();
    $table->text('observaciones')->nullable();
    $table->text('aspectos')->nullable();
    $table->text('compulsas')->nullable();

    $table->enum('estatus', ['en_juicio','pendiente','autorizado','en_proceso','concluido'])
          ->default('en_juicio');

    $table->timestamps();

    // FKs DESPUÉS DE LAS COLUMNAS
    $table->foreign('idempresa')
          ->references('idempresa')->on('empresas')
          ->cascadeOnDelete();

    $table->foreign('autoridad_id')
          ->references('id')->on('autoridades')
          ->nullOnDelete();

    $table->foreign('usuario_id')
          ->references('id')->on('users')
          ->nullOnDelete();
});
  }

  public function down(): void {
    Schema::dropIfExists('revisiones');
  }
};
