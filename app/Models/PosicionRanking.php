<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PosicionRanking extends Model
{
    protected $table = 'posiciones_ranking';

    protected $fillable = [
        'ranking_mensual_id',
        'puesto',
        'vendedor_id',
        'nombres_snapshot',
        'apellidos_snapshot',
        'celular_snapshot',
        'foto_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'puesto' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        $validarRankingEditable = function (PosicionRanking $posicion) {
            $ranking = $posicion->rankingMensual()->first();

            if (! $ranking || ! $ranking->puedeModificarse()) {
                throw new DomainException('No se pueden modificar las posiciones de un ranking cerrado, vencido o fuera del plazo de los 3 primeros días.');
            }

            if (! in_array((int) $posicion->puesto, [1, 2, 3], true)) {
                throw new DomainException('El puesto del ranking solo puede ser 1, 2 o 3.');
            }
        };

        static::creating($validarRankingEditable);
        static::updating($validarRankingEditable);
        static::deleting($validarRankingEditable);
    }

    public function rankingMensual(): BelongsTo
    {
        return $this->belongsTo(RankingMensual::class, 'ranking_mensual_id');
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(Vendedor::class, 'vendedor_id');
    }

    public function getNombreCompletoSnapshotAttribute(): string
    {
        return trim("{$this->nombres_snapshot} {$this->apellidos_snapshot}");
    }

    public function getCelularWhatsappSnapshotAttribute(): string
    {
        $soloDigitos = preg_replace('/[^0-9]/', '', $this->celular_snapshot) ?: '';

        if (strlen($soloDigitos) === 9 && str_starts_with($soloDigitos, '9')) {
            return '51' . $soloDigitos;
        }

        return $soloDigitos;
    }

    public function getUrlFotoSnapshotAttribute(): string
    {
        if (str_starts_with($this->foto_snapshot, 'http://') || str_starts_with($this->foto_snapshot, 'https://')) {
            return $this->foto_snapshot;
        }

        return Storage::disk('public')->url($this->foto_snapshot);
    }
}
