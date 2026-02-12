<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maquinas', function (Blueprint $table) {
            $table->id('id_maquina');
            $table->string('nombre', 150)->nullable();
            $table->string('estado', 50)->nullable();
            $table->string('ubicacion', 200)->nullable();
            $table->foreignId('administrador_id')
                ->nullable()
                ->constrained('administradores', 'id_administrador')
                ->onDelete('set null');
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict');
            $table->foreignId('ejercicio_id')
                ->nullable()
                ->constrained('ejercicios', 'id_ejercicio')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maquinas');
    }
};
