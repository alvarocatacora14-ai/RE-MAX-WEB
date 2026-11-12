<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->boolean('destacada')->default(false)->after('activo');
            $table->index(['activo', 'destacada', 'id'], 'idx_propiedades_activo_destacada');
        });
    }

    public function down(): void
    {
        Schema::table('propiedades', function (Blueprint $table) {
            $table->dropIndex('idx_propiedades_activo_destacada');
            $table->dropColumn('destacada');
        });
    }
};
