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
        // Create ENUM type for plan_tipo
        DB::statement("DROP TYPE IF EXISTS plan_tipo CASCADE");
        DB::statement("CREATE TYPE plan_tipo AS ENUM ('Mensual','Semestral','Anual')");

        Schema::create('planes', function (Blueprint $table) {
            $table->id('id_plan');
            $table->enum('tipo', ['Mensual', 'Semestral', 'Anual']);
            $table->date('fecha_inicio');
            $table->date('fecha_corte')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->decimal('valor', 12, 2);
            $table->foreignId('afiliado_id')
                ->constrained('afiliados', 'id_afiliado')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planes');
        DB::statement("DROP TYPE IF EXISTS plan_tipo");
    }
};
