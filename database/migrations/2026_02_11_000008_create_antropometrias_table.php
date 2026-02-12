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
        Schema::create('antropometrias', function (Blueprint $table) {
            $table->id('id_antropometria');
            $table->decimal('grasa_corporal', 5, 2)->nullable();
            $table->decimal('altura_cm', 6, 2)->nullable();
            $table->decimal('peso', 6, 2)->nullable();
            $table->decimal('imc', 5, 2)->nullable();
            $table->foreignId('afiliado_id')
                ->constrained('afiliados', 'id_afiliado')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->timestampTz('fecha_medicion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antropometrias');
    }
};
