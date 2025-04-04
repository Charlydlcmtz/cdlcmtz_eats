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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('username')->unique();
            $table->string('apellido_p');
            $table->string('apellido_m');
            $table->string('telefono');
            $table->string('no_empleado')->nullable();
            $table->string('img_user')->nullable();
            $table->unsignedBigInteger('id_empresa')->nullable(); // Relación con empresas
            $table->unsignedBigInteger('id_role');
            $table->string('correo')->unique();
            $table->timestamp('correo_verificado_at')->nullable();
            $table->string('password');
            $table->boolean('estatus')->default(true);
            $table->rememberToken();
            $table->timestamps();

            // Definir clave foránea
            $table->foreign('id_empresa')->references('id')->on('empresas')->onDelete('set null');
            $table->foreign('id_role')->references('id')->on('roles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
