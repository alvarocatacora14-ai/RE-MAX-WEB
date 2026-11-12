<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RankingMensual extends Model
{
    protected $table = 'rankings_mensuales';

    public const ESTADO_BORRADOR = 'BORRADOR';
    public const ESTADO_PUBLICADO = 'PUBLICADO';
    public const ESTADO_CERRADO = 'CERRADO';
    public const ESTADO_VENCIDO = 'VENCIDO';

    public const NOMBRES_MESES = [
        1 => 'Enero',
        2 => 'Febrero',
        3 => 'Marzo',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Junio',
        7 => 'Julio',
        8 => 'Agosto',
        9 => 'Septiembre',
        10 => 'Octubre',
        11 => 'Noviembre',
        12 => 'Diciembre',
    ];

    protected $fillable = [
        'mes_desempeno',
        'anio_desempeno',
        'mes_publicacion',
        'anio_publicacion',
        'estado',
        'fecha_publicacion',
        'fecha_cierre',
        'creado_por',
        'actualizado_por',
    ];

    protected function casts(): array
    {
        return [
            'mes_desempeno' => 'integer',
            'anio_desempeno' => 'integer',
            'mes_publicacion' => 'integer',
            'anio_publicacion' => 'integer',
            'fecha_publicacion' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (RankingMensual $ranking) {
            if (! self::dentroDePlazoDeGestion((int) $ranking->anio_publicacion, (int) $ranking->mes_publicacion)) {
                throw new DomainException('Solo se puede crear o preparar el ranking durante los 3 primeros días calendario del mes de publicación (America/Lima).');
            }
        });

        static::updating(function (RankingMensual $ranking) {
            $estadoOriginal = $ranking->getOriginal('estado');

            // Un ranking CERRADO o VENCIDO es 100% inmutable
            if (in_array($estadoOriginal, [self::ESTADO_CERRADO, self::ESTADO_VENCIDO], true)) {
                throw new DomainException('Los rankings cerrados o vencidos son históricos y no pueden modificarse.');
            }

            // Si estamos fuera del plazo de los 3 primeros días, solo se permite la transición interna controlada de cierre
            $anioPub = (int) $ranking->getOriginal('anio_publicacion');
            $mesPub = (int) $ranking->getOriginal('mes_publicacion');

            if (! self::dentroDePlazoDeGestion($anioPub, $mesPub)) {
                if (! $ranking->esTransicionInternaDeCierre()) {
                    throw new DomainException('El plazo de gestión del ranking (días 1 al 3 del mes) ha finalizado. El ranking se encuentra bloqueado.');
                }
            }
        });

        static::deleting(function (RankingMensual $ranking) {
            if (in_array($ranking->estado, [self::ESTADO_PUBLICADO, self::ESTADO_CERRADO, self::ESTADO_VENCIDO], true)) {
                throw new DomainException('Un ranking publicado, cerrado o vencido no puede ser eliminado por ningún usuario.');
            }

            if (! self::dentroDePlazoDeGestion((int) $ranking->anio_publicacion, (int) $ranking->mes_publicacion)) {
                throw new DomainException('No se puede eliminar un ranking fuera de los 3 primeros días del mes.');
            }
        });
    }

    /**
     * Calcula el período actual según la fecha de America/Lima.
     */
    public static function periodoActual(): array
    {
        $ahoraLima = now('America/Lima');
        $fechaDesempeno = $ahoraLima->copy()->startOfMonth()->subMonthNoOverflow();

        return [
            'mes_desempeno' => (int) $fechaDesempeno->month,
            'anio_desempeno' => (int) $fechaDesempeno->year,
            'mes_publicacion' => (int) $ahoraLima->month,
            'anio_publicacion' => (int) $ahoraLima->year,
        ];
    }

    /**
     * Verifica en tiempo real contra el reloj de America/Lima si hoy es día 1, 2 o 3
     * del mes y año de publicación indicados.
     */
    public static function dentroDePlazoDeGestion(int $anioPublicacion, int $mesPublicacion): bool
    {
        $ahora = now('America/Lima');

        return (int) $ahora->year === $anioPublicacion
            && (int) $ahora->month === $mesPublicacion
            && (int) $ahora->day >= 1
            && (int) $ahora->day <= 3;
    }

    public function puedeModificarse(): bool
    {
        if (in_array($this->estado, [self::ESTADO_CERRADO, self::ESTADO_VENCIDO], true)) {
            return false;
        }

        return self::dentroDePlazoDeGestion((int) $this->anio_publicacion, (int) $this->mes_publicacion);
    }

    /**
     * Excepción controlada únicamente para el paso interno PUBLICADO -> CERRADO o BORRADOR -> VENCIDO.
     */
    public function esTransicionInternaDeCierre(): bool
    {
        $estadoOriginal = $this->getOriginal('estado');
        $estadoNuevo = $this->estado;

        $transicionValida = ($estadoOriginal === self::ESTADO_PUBLICADO && $estadoNuevo === self::ESTADO_CERRADO)
                         || ($estadoOriginal === self::ESTADO_BORRADOR && $estadoNuevo === self::ESTADO_VENCIDO);

        if (! $transicionValida) {
            return false;
        }

        $camposModificados = array_keys($this->getDirty());
        $camposPermitidos = ['estado', 'fecha_cierre', 'updated_at'];

        return empty(array_diff($camposModificados, $camposPermitidos));
    }

    public function posiciones(): HasMany
    {
        return $this->hasMany(PosicionRanking::class, 'ranking_mensual_id')->orderBy('puesto');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'creado_por');
    }

    public function actualizador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'actualizado_por');
    }

    public function getEtiquetaDesempenoAttribute(): string
    {
        $mes = self::NOMBRES_MESES[$this->mes_desempeno] ?? $this->mes_desempeno;

        return "{$mes} {$this->anio_desempeno}";
    }

    public function getEtiquetaPublicacionAttribute(): string
    {
        $mes = self::NOMBRES_MESES[$this->mes_publicacion] ?? $this->mes_publicacion;

        return "{$mes} {$this->anio_publicacion}";
    }
}
