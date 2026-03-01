<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aplicacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_aplicacion')->default('GymApp');
            $table->string('link_icono')->nullable();
            $table->string('logo')->nullable();
            $table->string('gama_colores')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Insertar un registro inicial
        \DB::table('aplicacion')->insert([
            'nombre_aplicacion' => 'GymApp',
            'link_icono' => 'https://via.placeholder.com/100',
            'logo' => 'https://via.placeholder.com/200',
            'gama_colores' => '#f26329, #ff6b35, #FFA500',
            'descripcion' => 'Sistema de gestión integral para gimnasios',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicacion');
    }
};
