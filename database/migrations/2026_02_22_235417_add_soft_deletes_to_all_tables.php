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
        $tables = [
            'usuarios',
            'centro_deportivo',
            'contacto_emergencia',
            'afiliados',
            'profesionales',
            'trabajadores',
            'administradores',
            'planes',
            'pagos',
            'actividad_deportiva',
            'ejercicios',
            'rutinas',
            'antropometrias',
            'maquinas',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'usuarios',
            'centro_deportivo',
            'contacto_emergencia',
            'afiliados',
            'profesionales',
            'trabajadores',
            'administradores',
            'planes',
            'pagos',
            'actividad_deportiva',
            'ejercicios',
            'rutinas',
            'antropometrias',
            'maquinas',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropSoftDeletes();
            });
        }
    }
};
