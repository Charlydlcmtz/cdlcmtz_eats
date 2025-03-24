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
        Schema::create('paypal', function (Blueprint $table) {
            $table->id();
            $table->string('cuenta');
            $table->unsignedBigInteger('id_user'); // Relación con empresas
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
        Schema::dropIfExists('paypal');
    }
};
