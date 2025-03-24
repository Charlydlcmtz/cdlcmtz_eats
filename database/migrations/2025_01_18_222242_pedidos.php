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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->longText('descripcion')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable(); // Relación con empresas
            $table->unsignedBigInteger('id_user'); // Relación con usuarios
            $table->unsignedBigInteger('id_estatus'); // Relación con estatus
            $table->timestamps();
            // Definir clave foránea
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('id_estatus')->references('id')->on('catalogo_estatus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
