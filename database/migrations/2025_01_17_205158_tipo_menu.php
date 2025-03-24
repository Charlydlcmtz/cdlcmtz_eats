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
        Schema::create('tipo_menu', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_menu');
            $table->longText('descripcion_menu')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable(); // Relación con empresas
            $table->boolean('estatus')->default(true);
            $table->timestamps();
            // Definir clave foránea
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_menu');
    }
};
