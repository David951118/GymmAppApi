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
        Schema::create('rutinas', function (Blueprint $table) {
            $table->id('id_rutina');
            $table->string('nombre', 200)->nullable();
            $table->integer('sesiones_totales')->nullable();
            $table->integer('sesiones_restantes')->nullable();
            $table->foreignId('afiliado_id')
                ->constrained('afiliados', 'id_afiliado')
                ->onDelete('cascade');
            $table->foreignId('profesional_id')
                ->nullable()
                ->constrained('profesionales', 'id_profesional')
                ->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rutinas');
    }
};
