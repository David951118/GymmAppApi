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
        Schema::create('actividad_deportiva', function (Blueprint $table) {
            $table->id('id_actividad');
            $table->timestampTz('fecha');
            $table->string('tipo', 100)->nullable();
            $table->integer('duracion')->nullable();
            $table->foreignId('profesional_id')
                ->nullable()
                ->constrained('profesionales', 'id_profesional')
                ->onDelete('set null');
            $table->foreignId('centro_id')
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actividad_deportiva');
    }
};
