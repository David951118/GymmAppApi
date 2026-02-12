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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->timestampTz('fecha_cobro')->useCurrent();
            $table->string('estado', 50);
            $table->foreignId('plan_id')
                ->constrained('planes', 'id_plan')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->decimal('monto', 12, 2);
            $table->string('metodo_pago', 50)->nullable();
            $table->string('referencia', 200)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
