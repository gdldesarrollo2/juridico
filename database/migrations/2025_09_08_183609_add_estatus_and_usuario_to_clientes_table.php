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
        Schema::table('clientes', function (Blueprint $table) {
            $table->enum('estatus', ['activo','inactivo'])->default('activo');
            $table->unsignedBigInteger('usuario_id')->nullable();

            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['estatus','usuario_id']);
        });
    }
};
