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
        Schema::create('ingresa_afiliado_centro', function (Blueprint $table) {
            $table->foreignId('afiliado_id')
                ->constrained('afiliados', 'id_afiliado')
                ->onDelete('restrict');
            $table->foreignId('centro_id')
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict');
            $table->timestampTz('fecha_ingreso')->useCurrent();

            $table->primary(['afiliado_id', 'centro_id', 'fecha_ingreso']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresa_afiliado_centro');
    }
};
