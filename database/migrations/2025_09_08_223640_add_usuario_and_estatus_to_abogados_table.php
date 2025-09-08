<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('abogados', function (Blueprint $table) {
            $table->enum('estatus', ['activo','inactivo'])
                  ->default('activo')
                  ->after('nombre');
            
            $table->unsignedBigInteger('usuario_id')->nullable()->after('estatus');
            $table->foreign('usuario_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete(); // si borran el user, queda null
        });
    }

    public function down(): void
    {
        Schema::table('abogados', function (Blueprint $table) {
            $table->dropForeign(['usuario_id']);
            $table->dropColumn(['estatus','usuario_id']);
        });
    }
};
