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
        Schema::create('menu', function (Blueprint $table) {
            $table->id();
            $table->string('platillo');
            $table->longText('descripcion')->nullable();
            $table->decimal('costo', 10, 2);
            $table->string('calorias')->nullable();
            $table->longText('img_comida');
            $table->boolean('estatus')->default(true);
            $table->dateTime('inicio_fecha_platillo')->nullable();
            $table->dateTime('fin_fecha_platillo')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable(); // Relación con empresas
            $table->unsignedBigInteger('id_tipo_menu'); // Relación con tipo menu
            $table->timestamps();
            // Definir clave foránea
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('id_tipo_menu')->references('id')->on('tipo_menu')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::dropIfExists('menu');
    }
};
