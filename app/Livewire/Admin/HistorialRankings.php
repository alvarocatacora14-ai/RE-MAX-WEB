<?php

namespace App\Livewire\Admin;

use App\Models\RankingMensual;
use App\Services\ServicioRankingMensual;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Historial de Rankings | RE/MAX Zona')]
class HistorialRankings extends Component
{
    public ?int $anioFiltro = null;
    public ?int $mesFiltro = null;

    public function mount(ServicioRankingMensual $servicio): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $servicio->sincronizarEstadosVencidos();

        $ultimoRanking = RankingMensual::whereIn('estado', [
            RankingMensual::ESTADO_CERRADO,
            RankingMensual::ESTADO_PUBLICADO,
        ])
            ->orderByDesc('anio_desempeno')
            ->orderByDesc('mes_desempeno')
            ->first();

        if ($ultimoRanking) {
            $this->anioFiltro = $ultimoRanking->anio_desempeno;
            $this->mesFiltro = $ultimoRanking->mes_desempeno;
        } else {
            $periodo = RankingMensual::periodoActual();
            $this->anioFiltro = $periodo['anio_desempeno'];
            $this->mesFiltro = $periodo['mes_desempeno'];
        }
    }

    public function render()
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403);
        }

        $rankingConsultado = null;
        if ($this->anioFiltro && $this->mesFiltro) {
            $rankingConsultado = RankingMensual::with(['posiciones', 'creador'])
                ->where('anio_desempeno', (int) $this->anioFiltro)
                ->where('mes_desempeno', (int) $this->mesFiltro)
                ->whereIn('estado', [
                    RankingMensual::ESTADO_CERRADO,
                    RankingMensual::ESTADO_PUBLICADO,
                ])
                ->first();
        }

        $historialGeneral = RankingMensual::with('posiciones')
            ->whereIn('estado', [
                RankingMensual::ESTADO_CERRADO,
                RankingMensual::ESTADO_PUBLICADO,
            ])
            ->orderByDesc('anio_desempeno')
            ->orderByDesc('mes_desempeno')
            ->get();

        $aniosDisponibles = range((int) now('America/Lima')->year, 2024);

        return view('livewire.admin.historial-rankings', [
            'rankingConsultado' => $rankingConsultado,
            'historialGeneral' => $historialGeneral,
            'meses' => RankingMensual::NOMBRES_MESES,
            'aniosDisponibles' => $aniosDisponibles,
        ]);
    }
}
