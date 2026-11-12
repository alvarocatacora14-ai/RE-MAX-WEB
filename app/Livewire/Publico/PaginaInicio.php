<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use App\Models\ConfiguracionPortada;
use App\Models\Propiedad;
use App\Models\RankingMensual;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.publico')]
#[Title('RE/MAX Zona | Inmobiliaria')]
class PaginaInicio extends Component
{
    public function render()
    {
        $ahoraLima = now('America/Lima');

        // Consulta 100% de solo lectura (SELECT): obtiene el ranking del mes de publicación actual si ya fue publicado
        $rankingPublicado = RankingMensual::with('posiciones')
            ->where('anio_publicacion', (int) $ahoraLima->year)
            ->where('mes_publicacion', (int) $ahoraLima->month)
            ->whereIn('estado', [
                RankingMensual::ESTADO_PUBLICADO,
                RankingMensual::ESTADO_CERRADO,
            ])
            ->whereNotNull('fecha_publicacion')
            ->first();

        $propiedadesDestacadas = Propiedad::where('activo', true)
            ->where('destacada', true)
            ->orderBy('id')
            ->get();

        $contacto = ConfiguracionContacto::obtenerUnica();
        $portada = ConfiguracionPortada::obtenerUnica();

        return view('livewire.publico.pagina-inicio', [
            'rankingPublicado' => $rankingPublicado,
            'propiedadesDestacadas' => $propiedadesDestacadas,
            'contacto' => $contacto,
            'portada' => $portada,
        ]);
    }
}
