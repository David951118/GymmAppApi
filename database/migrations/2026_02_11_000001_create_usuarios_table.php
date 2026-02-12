<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create ENUM type for usuario_estado
        DB::statement("DROP TYPE IF EXISTS usuario_estado CASCADE");
        DB::statement("CREATE TYPE usuario_estado AS ENUM ('activo','inactivo','suspendido','retirado','bloqueado')");

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->string('contrasena', 255);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('celular', 30)->nullable();
            $table->string('correo', 255)->nullable()->unique();
            $table->string('cedula', 50)->nullable()->unique();
            $table->string('apellidos', 150)->nullable();
            $table->string('nombre', 150)->nullable();
            $table->enum('estado_usuario', ['activo', 'inactivo', 'suspendido', 'retirado', 'bloqueado'])
                ->default('activo');
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
        DB::statement("DROP TYPE IF EXISTS usuario_estado");
    }
};
