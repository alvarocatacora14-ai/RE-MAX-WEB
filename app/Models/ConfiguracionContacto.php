<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionContacto extends Model
{
    protected $table = 'configuracion_contacto';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nombre_empresa',
        'telefono_visible',
        'whatsapp_general',
        'correo_contacto',
        'direccion',
        'horario_lunes_viernes',
        'horario_sabados',
        'whatsapp_postulacion',
    ];

    protected static function booted(): void
    {
        static::creating(function (ConfiguracionContacto $config) {
            $config->id = 1;
            if (static::where('id', 1)->exists()) {
                throw new DomainException('Solo puede existir un único registro de configuración de contacto.');
            }
        });

        static::updating(function (ConfiguracionContacto $config) {
            $config->id = 1;
        });

        static::deleting(function () {
            throw new DomainException('El registro único de configuración de contacto no puede eliminarse.');
        });
    }

    public static function obtenerUnica(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'nombre_empresa' => 'RE/MAX Zona',
                'telefono_visible' => '+51 999 999 999',
                'whatsapp_general' => '51999999999',
                'correo_contacto' => 'contacto@remaxzona.com',
                'direccion' => 'Sur del Perú (Arequipa, Lima, Moquegua)',
                'horario_lunes_viernes' => 'Lunes a viernes: 9:00 a. m. – 6:00 p. m.',
                'horario_sabados' => 'Sábados: 9:00 a. m. – 1:00 p. m.',
                'whatsapp_postulacion' => '51999999999',
            ]
        );
    }

    public function getTelefonoEnlaceAttribute(): string
    {
        return preg_replace('/[^0-9+]/', '', $this->telefono_visible) ?: '+51999999999';
    }
}
