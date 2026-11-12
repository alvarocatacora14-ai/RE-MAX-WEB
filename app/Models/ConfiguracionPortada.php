<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ConfiguracionPortada extends Model
{
    protected $table = 'configuracion_portada';

    public $incrementing = false;

    public const IMAGEN_POR_DEFECTO = 'images/fondoejemplo.jpg';

    protected $fillable = [
        'id',
        'imagen',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConfiguracionPortada $portada) {
            $portada->id = 1;
            if (static::where('id', 1)->exists()) {
                throw new DomainException('Solo puede existir un único registro de configuración de portada.');
            }
        });

        static::updating(function (ConfiguracionPortada $portada) {
            $portada->id = 1;
        });

        static::deleting(function () {
            throw new DomainException('El registro único de configuración de portada no puede eliminarse.');
        });
    }

    public static function obtenerUnica(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'imagen' => self::IMAGEN_POR_DEFECTO,
            ]
        );
    }

    public function getUrlImagenAttribute(): string
    {
        if ($this->imagen === null || trim($this->imagen) === '') {
            return asset(self::IMAGEN_POR_DEFECTO);
        }

        if (str_starts_with($this->imagen, 'http://') || str_starts_with($this->imagen, 'https://')) {
            return $this->imagen;
        }

        if (str_starts_with($this->imagen, 'images/')) {
            return file_exists(public_path($this->imagen))
                ? asset($this->imagen)
                : asset(self::IMAGEN_POR_DEFECTO);
        }

        if (Storage::disk('public')->exists($this->imagen)) {
            return Storage::disk('public')->url($this->imagen);
        }

        return asset(self::IMAGEN_POR_DEFECTO);
    }
}
