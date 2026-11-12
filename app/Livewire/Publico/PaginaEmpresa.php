<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use App\Models\RankingMensual;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.publico')]
#[Title('Empresa | RE/MAX Zona')]
class PaginaEmpresa extends Component
{
    public function render()
    {
        $ahoraLima = now('America/Lima');

        $rankingPublicado = RankingMensual::with('posiciones')
            ->where('anio_publicacion', (int) $ahoraLima->year)
            ->where('mes_publicacion', (int) $ahoraLima->month)
            ->whereIn('estado', [
                RankingMensual::ESTADO_PUBLICADO,
                RankingMensual::ESTADO_CERRADO,
            ])
            ->whereNotNull('fecha_publicacion')
            ->first();

        $contacto = ConfiguracionContacto::obtenerUnica();

        return view('livewire.publico.pagina-empresa', [
            'rankingPublicado' => $rankingPublicado,
            'contacto' => $contacto,
        ]);
    }
}
