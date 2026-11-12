<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->enum('oficina', ['AREQUIPA', 'LIMA', 'MOQUEGUA', 'PUNO'])
                ->nullable()
                ->after('ubicacion');

            $table->index(['activo', 'oficina', 'id'], 'idx_propiedades_activo_oficina');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropIndex('idx_propiedades_activo_oficina');
            $table->dropColumn('oficina');
        });
    }
};
