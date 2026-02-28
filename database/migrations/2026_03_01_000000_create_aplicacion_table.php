<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('aplicacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_aplicacion')->default('GymApp');
            $table->string('link_icono')->nullable();
            $table->string('gama_colores')->default('red,blue');
            $table->string('logo')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplicacion');
    }
};
