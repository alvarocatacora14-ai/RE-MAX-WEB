<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public const ROL_ADMINISTRADOR = 'ADMINISTRADOR';
    public const ROL_MARKETING = 'MARKETING';

    protected $fillable = [
        'nombre',
        'correo',
        'password',
        'rol',
        'activo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function esAdministrador(): bool
    {
        return $this->rol === self::ROL_ADMINISTRADOR;
    }

    public function esMarketing(): bool
    {
        return $this->rol === self::ROL_MARKETING;
    }

    public function rankingsCreados(): HasMany
    {
        return $this->hasMany(RankingMensual::class, 'creado_por');
    }

    public function rankingsActualizados(): HasMany
    {
        return $this->hasMany(RankingMensual::class, 'actualizado_por');
    }
}
