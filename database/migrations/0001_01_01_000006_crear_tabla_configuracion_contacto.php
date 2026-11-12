<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_contacto', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->default(1)->primary();
            $table->string('nombre_empresa', 120)->default('RE/MAX Zona');
            $table->string('telefono_visible', 30);
            $table->string('whatsapp_general', 20);
            $table->string('correo_contacto', 150);
            $table->string('direccion', 255)->nullable();
            $table->string('horario_lunes_viernes', 120);
            $table->string('horario_sabados', 120);
            $table->string('whatsapp_postulacion', 20);
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE `configuracion_contacto`
            ADD CONSTRAINT `chk_configuracion_unica`
            CHECK (`id` = 1)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_contacto');
    }
};
