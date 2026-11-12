<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Oficina extends Model
{
    protected $table = 'oficinas';

    public const CODIGO_AREQUIPA = 'AREQUIPA';
    public const CODIGO_LIMA = 'LIMA';
    public const CODIGO_MOQUEGUA = 'MOQUEGUA';
    public const CODIGO_PUNO = 'PUNO';

    public const CODIGOS_SLUGS = [
        self::CODIGO_AREQUIPA => 'arequipa',
        self::CODIGO_LIMA => 'lima',
        self::CODIGO_MOQUEGUA => 'moquegua',
        self::CODIGO_PUNO => 'puno',
    ];

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'whatsapp',
        'correo',
        'horario',
        'enlace_google_maps',
        'imagen',
    ];

    protected static function booted(): void
    {
        static::creating(function (Oficina $oficina) {
            if (
                ! isset(self::CODIGOS_SLUGS[$oficina->codigo])
                || self::CODIGOS_SLUGS[$oficina->codigo] !== $oficina->slug
            ) {
                throw new DomainException('Solo se permiten las cuatro oficinas oficiales del sistema.');
            }

            if (static::where('codigo', $oficina->codigo)->orWhere('slug', $oficina->slug)->exists()) {
                throw new DomainException('Ya existe una oficina registrada con este código o slug.');
            }
        });

        static::updating(function (Oficina $oficina) {
            if ($oficina->isDirty('codigo') || $oficina->isDirty('slug')) {
                throw new DomainException('El código y el slug de la oficina no pueden modificarse.');
            }
        });

        static::deleting(function () {
            throw new DomainException('Las cuatro oficinas oficiales del sistema no pueden eliminarse.');
        });
    }

    public function propiedades(): HasMany
    {
        return $this->hasMany(Propiedad::class, 'oficina', 'codigo');
    }

    public static function obtenerTodas(): Collection
    {
        return static::query()
            ->orderByRaw("CASE codigo
                WHEN 'AREQUIPA' THEN 1
                WHEN 'LIMA' THEN 2
                WHEN 'MOQUEGUA' THEN 3
                WHEN 'PUNO' THEN 4
                ELSE 5 END")
            ->get();
    }

    public static function porSlug(string $slug): ?self
    {
        $slugNormalizado = strtolower(trim($slug));

        if (! in_array($slugNormalizado, self::CODIGOS_SLUGS, true)) {
            return null;
        }

        return static::where('slug', $slugNormalizado)->first();
    }

    public function getUrlImagenAttribute(): ?string
    {
        if ($this->imagen === null || trim($this->imagen) === '') {
            return null;
        }

        if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
            return $this->imagen;
        }

        if (str_starts_with($this->imagen, 'images/')) {
            return file_exists(public_path($this->imagen)) ? asset($this->imagen) : null;
        }

        if (Storage::disk('public')->exists($this->imagen)) {
            return Storage::disk('public')->url($this->imagen);
        }

        return null;
    }

    public function getTelefonoEnlaceAttribute(): ?string
    {
        if ($this->telefono === null || trim($this->telefono) === '') {
            return null;
        }

        $limpio = preg_replace('/[^0-9+]/', '', $this->telefono);

        return $limpio !== '' ? 'tel:' . $limpio : null;
    }

    public function getWhatsappEnlaceAttribute(): ?string
    {
        if ($this->whatsapp === null || trim($this->whatsapp) === '') {
            return null;
        }

        $soloDigitos = preg_replace('/[^0-9]/', '', $this->whatsapp) ?: '';

        if ($soloDigitos === '') {
            return null;
        }

        if (strlen($soloDigitos) === 9 && str_starts_with($soloDigitos, '9')) {
            $soloDigitos = '51' . $soloDigitos;
        }

        return 'https://wa.me/' . $soloDigitos;
    }

    public function getTieneDatosContactoAttribute(): bool
    {
        return filled($this->direccion)
            || filled($this->telefono)
            || filled($this->whatsapp)
            || filled($this->correo)
            || filled($this->horario)
            || filled($this->enlace_google_maps);
    }
}
