<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posiciones_ranking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranking_mensual_id')
                ->constrained('rankings_mensuales')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('puesto');
            $table->foreignId('vendedor_id')
                ->constrained('vendedores')
                ->restrictOnDelete();
            $table->string('nombres_snapshot', 100);
            $table->string('apellidos_snapshot', 100);
            $table->string('celular_snapshot', 20);
            $table->string('foto_snapshot', 255);
            $table->timestamps();

            $table->unique(['ranking_mensual_id', 'puesto'], 'uk_posicion_puesto');
            $table->unique(['ranking_mensual_id', 'vendedor_id'], 'uk_posicion_vendedor');
        });

        DB::statement("
            ALTER TABLE `posiciones_ranking`
            ADD CONSTRAINT `chk_posicion_puesto`
            CHECK (`puesto` IN (1, 2, 3))
        ");

        // Triggers MySQL para proteger las posiciones si el ranking padre está CERRADO o VENCIDO
        DB::unprepared("
            CREATE TRIGGER `trg_posiciones_bloqueo_insert`
            BEFORE INSERT ON `posiciones_ranking`
            FOR EACH ROW
            BEGIN
                DECLARE v_estado VARCHAR(20);
                SELECT `estado` INTO v_estado FROM `rankings_mensuales` WHERE `id` = NEW.ranking_mensual_id;
                IF v_estado IN ('CERRADO', 'VENCIDO') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'No se pueden agregar posiciones a un ranking cerrado o vencido.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `trg_posiciones_bloqueo_update`
            BEFORE UPDATE ON `posiciones_ranking`
            FOR EACH ROW
            BEGIN
                DECLARE v_estado VARCHAR(20);
                SELECT `estado` INTO v_estado FROM `rankings_mensuales` WHERE `id` = OLD.ranking_mensual_id;
                IF v_estado IN ('CERRADO', 'VENCIDO') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'No se pueden modificar las posiciones de un ranking cerrado o vencido.';
                END IF;
            END
        ");

        DB::unprepared("
            CREATE TRIGGER `trg_posiciones_bloqueo_delete`
            BEFORE DELETE ON `posiciones_ranking`
            FOR EACH ROW
            BEGIN
                DECLARE v_estado VARCHAR(20);
                SELECT `estado` INTO v_estado FROM `rankings_mensuales` WHERE `id` = OLD.ranking_mensual_id;
                IF v_estado IN ('CERRADO', 'VENCIDO') THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'No se pueden eliminar las posiciones de un ranking cerrado o vencido.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_posiciones_bloqueo_insert`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_posiciones_bloqueo_update`");
        DB::unprepared("DROP TRIGGER IF EXISTS `trg_posiciones_bloqueo_delete`");
        Schema::dropIfExists('posiciones_ranking');
    }
};
