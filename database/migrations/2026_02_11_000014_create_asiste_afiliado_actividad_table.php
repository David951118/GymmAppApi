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
        Schema::create('asiste_afiliado_actividad', function (Blueprint $table) {
            $table->foreignId('afiliado_id')
                ->constrained('afiliados', 'id_afiliado')
                ->onDelete('restrict');
            $table->foreignId('actividad_id')
                ->constrained('actividad_deportiva', 'id_actividad')
                ->onDelete('restrict');
            $table->timestampTz('fecha_asistencia')->useCurrent();
            $table->timestampTz('fecha_inscripcion')->nullable();

            $table->primary(['afiliado_id', 'actividad_id', 'fecha_asistencia']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asiste_afiliado_actividad');
    }
};
