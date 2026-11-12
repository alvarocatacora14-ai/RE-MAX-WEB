<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankings_mensuales', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('mes_desempeno');
            $table->unsignedSmallInteger('anio_desempeno');
            $table->unsignedTinyInteger('mes_publicacion');
            $table->unsignedSmallInteger('anio_publicacion');
            $table->enum('estado', ['BORRADOR', 'PUBLICADO', 'CERRADO', 'VENCIDO'])->default('BORRADOR');
            $table->timestamp('fecha_publicacion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();
            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->foreignId('actualizado_por')
                ->nullable()
                ->constrained('usuarios')
                ->nullOnDelete();
            $table->timestamps();

            $table->unique(['anio_desempeno', 'mes_desempeno'], 'uk_ranking_desempeno');
            $table->unique(['anio_publicacion', 'mes_publicacion'], 'uk_ranking_publicacion');
        });

        DB::statement("
            ALTER TABLE `rankings_mensuales`
            ADD CONSTRAINT `chk_ranking_meses`
            CHECK (
                `mes_desempeno` BETWEEN 1 AND 12
                AND `mes_publicacion` BETWEEN 1 AND 12
                AND `anio_desempeno` BETWEEN 2020 AND 2100
                AND `anio_publicacion` BETWEEN 2020 AND 2101
            )
        ");

        DB::statement("
            ALTER TABLE `rankings_mensuales`
            ADD CONSTRAINT `chk_ranking_sucesion`
            CHECK (
                (`mes_desempeno` < 12 AND `mes_publicacion` = `mes_desempeno` + 1 AND `anio_publicacion` = `anio_desempeno`)
                OR
                (`mes_desempeno` = 12 AND `mes_publicacion` = 1 AND `anio_publicacion` = `anio_desempeno` + 1)
            )
        ");

        // Trigger MySQL para impedir modificar un ranking ya CERRADO o VENCIDO incluso vía Query Builder
        DB::unprepared("
            CREATE TRIGGER `trg_rankings_bloqueo_update`
            BEFORE UPDATE ON `rankings_mensuales`
            FOR EACH ROW
            BEGIN
                IF OLD.estado IN ('CERRADO', 'VENCIDO') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Un ranking mensual cerrado o vencido es inmutable y no puede modificarse.';
                END IF;
            END
        ");

        // Trigger MySQL para impedir eliminar rankings PUBLICADOS, CERRADOS o VENCIDOS
        DB::unprepared("
            CREATE TRIGGER `trg_rankings_bloqueo_delete`
            BEFORE DELETE ON `rankings_mensuales`
            FOR EACH ROW
            BEGIN
                IF OLD.estado IN ('PUBLICADO', 'CERRADO', 'VENCIDO') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Los rankings publicados o historicos no pueden eliminarse.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_rankings_bloqueo_update`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_rankings_bloqueo_delete`");
        Schema::dropIfExists('rankings_mensuales');
    }
};
