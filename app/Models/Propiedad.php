<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Propiedad extends Model
{
    protected $table = 'propiedades';

    public const MONEDA_SOLES = 'PEN';
    public const MONEDA_DOLARES = 'USD';

    public const OFICINA_AREQUIPA = 'AREQUIPA';
    public const OFICINA_LIMA = 'LIMA';
    public const OFICINA_MOQUEGUA = 'MOQUEGUA';
    public const OFICINA_PUNO = 'PUNO';

    public const OFICINAS = [
        self::OFICINA_AREQUIPA => 'Arequipa',
        self::OFICINA_LIMA => 'Lima',
        self::OFICINA_MOQUEGUA => 'Moquegua',
        self::OFICINA_PUNO => 'Puno',
    ];

    public const SLUGS_OFICINAS = [
        'arequipa' => self::OFICINA_AREQUIPA,
        'lima' => self::OFICINA_LIMA,
        'moquegua' => self::OFICINA_MOQUEGUA,
        'puno' => self::OFICINA_PUNO,
    ];

    protected $fillable = [
        'nombre',
        'imagen',
        'ubicacion',
        'oficina',
        'precio',
        'moneda',
        'enlace_remax',
        'activo',
        'destacada',
    ];

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'activo' => 'boolean',
            'destacada' => 'boolean',
        ];
    }

    public function getSimboloMonedaAttribute(): ?string
    {
        if ($this->precio === null || $this->moneda === null) {
            return null;
        }

        return $this->moneda === self::MONEDA_SOLES ? 'S/' : '$';
    }

    public function getPrecioFormateadoAttribute(): ?string
    {
        if ($this->precio === null || $this->moneda === null) {
            return null;
        }

        return $this->simbolo_moneda . ' ' . number_format((float) $this->precio, 2, '.', ',');
    }

    public function getUrlImagenAttribute(): string
    {
        if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
            return $this->imagen;
        }

        return Storage::disk('public')->url($this->imagen);
    }

    public function oficinaResponsable(): BelongsTo
    {
        return $this->belongsTo(Oficina::class, 'oficina', 'codigo');
    }

    public function getNombreOficinaAttribute(): ?string
    {
        if ($this->oficina === null) {
            return null;
        }

        return self::OFICINAS[$this->oficina] ?? null;
    }
}
