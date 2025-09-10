<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            // PK legacy
            $table->increments('idempresa');

            $table->integer('idsociedad_sucop')->nullable()->default(0);
            $table->integer('idplaza')->nullable()->default(0);

            $table->string('rfc', 13)->unique();
            $table->string('razonsocial', 150)->nullable();
            $table->string('empabrevia', 150)->nullable();

            $table->string('tiposociedad', 5);
            $table->string('tipoesquema', 10)->nullable(); // si quieres NO NULL pon ->default('');
            $table->boolean('activo')->default(true);

            $table->string('replegal', 50);
            $table->string('domicilio', 100)->nullable();
            $table->string('colonia', 50)->nullable();
            $table->string('ciudad', 100)->nullable();

            $table->char('estado', 2)->nullable()->default('X');
            $table->char('pais', 2)->nullable()->default('MX');

            $table->string('codpostal', 5);
            $table->char('tipo', 1)->nullable()->default('P');

            $table->string('recibos', 30)->nullable();
            $table->string('factura', 20)->nullable();
            $table->char('fintegracion', 10)->nullable();

            $table->string('calle', 60);
            $table->string('exterior', 20);
            $table->string('interior', 50)->nullable();
            $table->string('delegacion', 50);
            $table->char('zg', 1);

            $table->string('curp', 20);
            $table->string('paterno', 45);
            $table->string('materno', 45);
            $table->string('nombre', 45);
            $table->string('oficio', 5);

            $table->string('usuario', 20)->nullable();
            $table->string('pass', 50)->nullable();
            $table->string('correo', 50)->nullable();
            $table->string('cadena_c', 50)->nullable();
            $table->string('host_c', 100)->nullable();
            $table->char('no_certificado', 20)->nullable();

            // OJO: la tabla legacy no tiene timestamps; no los añadimos
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
