<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('oficinas', function (Blueprint $table) {
            $table->id();
            $table->enum('codigo', ['AREQUIPA', 'LIMA', 'MOQUEGUA', 'PUNO'])->unique('uk_oficinas_codigo');
            $table->string('slug', 30)->unique('uk_oficinas_slug');
            $table->string('nombre', 80);
            $table->string('direccion', 255)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('correo', 150)->nullable();
            $table->string('horario', 180)->nullable();
            $table->string('enlace_google_maps', 500)->nullable();
            $table->string('imagen', 255)->nullable();
            $table->timestamps();
        });

        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("
                ALTER TABLE `oficinas`
                ADD CONSTRAINT `chk_oficinas_codigo_slug`
                CHECK (
                    (`codigo` = 'AREQUIPA' AND `slug` = 'arequipa')
                    OR (`codigo` = 'LIMA' AND `slug` = 'lima')
                    OR (`codigo` = 'MOQUEGUA' AND `slug` = 'moquegua')
                    OR (`codigo` = 'PUNO' AND `slug` = 'puno')
                )
            ");
        }

        $ahora = now();

        DB::table('oficinas')->insert([
            [
                'codigo' => 'AREQUIPA',
                'slug' => 'arequipa',
                'nombre' => 'Arequipa',
                'direccion' => null,
                'telefono' => null,
                'whatsapp' => null,
                'correo' => null,
                'horario' => null,
                'enlace_google_maps' => null,
                'imagen' => 'images/Arequipa.jpg',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'codigo' => 'LIMA',
                'slug' => 'lima',
                'nombre' => 'Lima',
                'direccion' => null,
                'telefono' => null,
                'whatsapp' => null,
                'correo' => null,
                'horario' => null,
                'enlace_google_maps' => null,
                'imagen' => 'images/Lima.jpg',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'codigo' => 'MOQUEGUA',
                'slug' => 'moquegua',
                'nombre' => 'Moquegua',
                'direccion' => null,
                'telefono' => null,
                'whatsapp' => null,
                'correo' => null,
                'horario' => null,
                'enlace_google_maps' => null,
                'imagen' => 'images/Ilo.jpg',
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
            [
                'codigo' => 'PUNO',
                'slug' => 'puno',
                'nombre' => 'Puno',
                'direccion' => null,
                'telefono' => null,
                'whatsapp' => null,
                'correo' => null,
                'horario' => null,
                'enlace_google_maps' => null,
                'imagen' => null,
                'created_at' => $ahora,
                'updated_at' => $ahora,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('oficinas');
    }
};
