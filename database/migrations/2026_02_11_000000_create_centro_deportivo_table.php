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
        Schema::create('centro_deportivo', function (Blueprint $table) {
            $table->id('id_centro');
            $table->string('horario', 200)->nullable();
            $table->text('ubicacion');
            $table->string('nombre', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('centro_deportivo');
    }
};
