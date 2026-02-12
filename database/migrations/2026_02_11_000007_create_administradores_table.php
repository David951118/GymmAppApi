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
        Schema::create('administradores', function (Blueprint $table) {
            $table->id('id_administrador');
            $table->foreignId('usuario_id')
                ->nullable()
                ->unique()
                ->constrained('usuarios', 'id_usuario')
                ->onDelete('set null')
                ->onUpdate('cascade');
            $table->foreignId('centro_id')
                ->nullable()
                ->constrained('centro_deportivo', 'id_centro')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->string('nivel', 50)->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->boolean('is_super_admin')->default(false);
            $table->timestamps();

            // Index for super admin queries
            $table->index('is_super_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administradores');
    }
};
