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
        Schema::table('abogados', function (Blueprint $table) {
            // 1) email opcional, por si quieres enviar sin users
            if (!Schema::hasColumn('abogados', 'email')) {
                $table->string('email')->nullable()->after('nombre');
            }

            // 2) relación opcional a users
            if (!Schema::hasColumn('abogados', 'usuario_id')) {
                $table->foreignId('usuario_id')
                      ->nullable()
                      ->after('email')
                      ->constrained('users')
                      ->nullOnDelete()
                      ->cascadeOnUpdate();
                      // Si quieres imponer 1:1 (un user solo puede ser un abogado):
                      // ->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('abogados', function (Blueprint $table) {
            if (Schema::hasColumn('abogados', 'usuario_id')) {
                $table->dropConstrainedForeignId('usuario_id');
            }
            if (Schema::hasColumn('abogados', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
