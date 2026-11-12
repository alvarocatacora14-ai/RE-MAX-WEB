<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 140);
            $table->string('imagen', 255);
            $table->string('ubicacion', 140);
            $table->decimal('precio', 12, 2)->unsigned()->nullable();
            $table->enum('moneda', ['PEN', 'USD'])->nullable();
            $table->string('enlace_remax', 500);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index(['activo', 'id'], 'idx_propiedades_activo');
        });

        DB::statement("
            ALTER TABLE `propiedades`
            ADD CONSTRAINT `chk_propiedades_precio_moneda`
            CHECK (
                (`precio` IS NULL AND `moneda` IS NULL)
                OR
                (`precio` IS NOT NULL AND `precio` >= 0 AND `moneda` IS NOT NULL AND `moneda` IN ('PEN', 'USD'))
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades');
    }
};
