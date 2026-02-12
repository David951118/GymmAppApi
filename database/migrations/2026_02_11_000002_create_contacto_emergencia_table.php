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
        Schema::create('contacto_emergencia', function (Blueprint $table) {
            $table->id('id_contacto');
            $table->string('celular', 30);
            $table->string('nombre', 150);
            $table->foreignId('usuario_id')
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacto_emergencia');
    }
};
