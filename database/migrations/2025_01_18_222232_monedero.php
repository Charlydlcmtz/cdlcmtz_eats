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
        Schema::create('monedero', function (Blueprint $table) {
            $table->id();
            $table->boolean('metodo_pago')->nullable();
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            // Definir clave foránea
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monedero');
    }
};
