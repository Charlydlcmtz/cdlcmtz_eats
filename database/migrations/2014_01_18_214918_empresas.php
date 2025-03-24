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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('rfc')->unique();
            $table->string('telefono');
            $table->string('correo')->unique();
            $table->string('icon')->nullable();
            $table->string('colors')->nullable();
            $table->timestamp('correo_verificado_at')->nullable();
            $table->unsignedBigInteger('id_role');
            $table->string('password');
            $table->boolean('estatus')->default(true);
            $table->timestamps();

            $table->foreign('id_role')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
