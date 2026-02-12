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
        Schema::create('rutina_contiene_ejercicio', function (Blueprint $table) {
            $table->foreignId('rutina_id')
                ->constrained('rutinas', 'id_rutina')
                ->onDelete('cascade');
            $table->foreignId('ejercicio_id')
                ->constrained('ejercicios', 'id_ejercicio')
                ->onDelete('restrict');
            $table->integer('repeticiones')->nullable();
            $table->integer('series')->nullable();

            $table->primary(['rutina_id', 'ejercicio_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutina_contiene_ejercicio');
    }
};
