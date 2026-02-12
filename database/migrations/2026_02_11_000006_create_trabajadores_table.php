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
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id('id_trabajador');
            $table->foreignId('usuario_id')
                ->nullable()
                ->unique()
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('puesto', 150)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajadores');
    }
};
