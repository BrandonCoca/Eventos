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
        Schema::create('inscripcions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_id')
                ->constrained('registros')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('evento_id')
                ->constrained('eventos')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('participante_id')
                ->constrained('participantes')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('estado')->default(true);
            $table->dateTime('fecha')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripcions');
    }
};
