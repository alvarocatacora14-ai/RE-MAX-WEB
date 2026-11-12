<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('correo', 150)->unique('uk_usuarios_correo');
            $table->string('password', 255);
            $table->enum('rol', ['ADMINISTRADOR', 'MARKETING'])->default('MARKETING')->index('idx_usuarios_rol');
            $table->boolean('activo')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE `usuarios`
            ADD CONSTRAINT `chk_usuarios_rol`
            CHECK (`rol` IN ('ADMINISTRADOR', 'MARKETING'))
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
