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
        Schema::create('afiliados', function (Blueprint $table) {
            $table->id('id_afiliado');
            $table->foreignId('usuario_id')
                ->unique()
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Centro deportivo inicial (OBLIGATORIO)
            $table->foreignId('centro_id')
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliados');
    }
};
