<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_portada', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->default(1)->primary();
            $table->string('imagen', 255)->default('images/fondoejemplo.jpg');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("
                ALTER TABLE `configuracion_portada`
                ADD CONSTRAINT `chk_configuracion_portada_unica`
                CHECK (`id` = 1)
            ");
        }

        $ahora = now();

        DB::table('configuracion_portada')->insert([
            'id' => 1,
            'imagen' => 'images/fondoejemplo.jpg',
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_portada');
    }
};
