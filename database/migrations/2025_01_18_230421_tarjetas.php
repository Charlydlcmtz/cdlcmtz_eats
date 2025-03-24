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
        Schema::create('tarjetas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_tarjeta');
            $table->string('fecha_expiracion')->nullable();
            $table->string('cvv');
            $table->unsignedBigInteger('id_user'); // Relación con tarjetas
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            // Definir clave foránea
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarjetas');
    }
};
