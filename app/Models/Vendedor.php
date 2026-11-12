<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Vendedor extends Model
{
    protected $table = 'vendedores';

    protected $fillable = [
        'nombres',
        'apellidos',
        'celular',
        'foto',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function posicionesHistoricas(): HasMany
    {
        return $this->hasMany(PosicionRanking::class, 'vendedor_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }

    public function getCelularWhatsappAttribute(): string
    {
        $soloDigitos = preg_replace('/[^0-9]/', '', $this->celular) ?: '';

        if (strlen($soloDigitos) === 9 && str_starts_with($soloDigitos, '9')) {
            return '51' . $soloDigitos;
        }

        return $soloDigitos;
    }

    public function getUrlFotoAttribute(): string
    {
        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://')) {
            return $this->foto;
        }

        return Storage::disk('public')->url($this->foto);
    }
}
