<?php

namespace App\Livewire\Admin;

use App\Models\RankingMensual;
use App\Models\Vendedor;
use App\Services\ServicioRankingMensual;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Ranking Mensual | RE/MAX Zona')]
class GestionRankingActual extends Component
{
    public ?int $puesto1 = null;
    public ?int $puesto2 = null;
    public ?int $puesto3 = null;

    public ?string $mensajeExito = null;
    public ?string $mensajeError = null;

    public function mount(ServicioRankingMensual $servicio): void
    {
        $servicio->sincronizarEstadosVencidos();
        $this->cargarDatosDelPeriodoActual();
    }

    private function cargarDatosDelPeriodoActual(): void
    {
        $periodo = RankingMensual::periodoActual();

        $ranking = RankingMensual::with('posiciones')
            ->where('anio_desempeno', $periodo['anio_desempeno'])
            ->where('mes_desempeno', $periodo['mes_desempeno'])
            ->first();

        if ($ranking) {
            $posiciones = $ranking->posiciones->keyBy('puesto');
            $this->puesto1 = $posiciones->get(1)?->vendedor_id;
            $this->puesto2 = $posiciones->get(2)?->vendedor_id;
            $this->puesto3 = $posiciones->get(3)?->vendedor_id;
        }
    }

    public function guardarBorrador(ServicioRankingMensual $servicio): void
    {
        $this->procesarGuardado($servicio, false);
    }

    public function publicarRanking(ServicioRankingMensual $servicio): void
    {
        $this->procesarGuardado($servicio, true);
    }

    private function procesarGuardado(ServicioRankingMensual $servicio, bool $publicarDefinitivamente): void
    {
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $servicio->sincronizarEstadosVencidos();
        $periodo = RankingMensual::periodoActual();

        if (! RankingMensual::dentroDePlazoDeGestion($periodo['anio_publicacion'], $periodo['mes_publicacion'])) {
            $this->mensajeError = 'El plazo de gestión (días 1 al 3 del mes) ha finalizado. El ranking se encuentra bloqueado.';
            return;
        }

        $this->validate([
            'puesto1' => ['required', 'integer', 'exists:vendedores,id', 'different:puesto2', 'different:puesto3'],
            'puesto2' => ['required', 'integer', 'exists:vendedores,id', 'different:puesto1', 'different:puesto3'],
            'puesto3' => ['required', 'integer', 'exists:vendedores,id', 'different:puesto1', 'different:puesto2'],
        ], [
            'puesto1.required' => 'Selecciona al vendedor del Primer Puesto.',
            'puesto2.required' => 'Selecciona al vendedor del Segundo Puesto.',
            'puesto3.required' => 'Selecciona al vendedor del Tercer Puesto.',
            'puesto1.different' => 'Un mismo vendedor no puede ocupar dos puestos en el mismo ranking.',
            'puesto2.different' => 'Un mismo vendedor no puede ocupar dos puestos en el mismo ranking.',
            'puesto3.different' => 'Un mismo vendedor no puede ocupar dos puestos en el mismo ranking.',
        ]);

        try {
            $ranking = $servicio->guardarOPublicarRanking(
                [
                    1 => (int) $this->puesto1,
                    2 => (int) $this->puesto2,
                    3 => (int) $this->puesto3,
                ],
                (int) Auth::id(),
                $publicarDefinitivamente
            );

            $this->mensajeExito = $ranking->estado === RankingMensual::ESTADO_PUBLICADO
                ? 'Ranking publicado y actualizado correctamente en el sitio público.'
                : 'Borrador del ranking guardado correctamente.';

            $this->cargarDatosDelPeriodoActual();
        } catch (DomainException $e) {
            $this->mensajeError = $e->getMessage();
        } catch (Throwable $e) {
            $this->mensajeError = 'Ocurrió un error al procesar el ranking: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $periodo = RankingMensual::periodoActual();
        $dentroDePlazo = RankingMensual::dentroDePlazoDeGestion($periodo['anio_publicacion'], $periodo['mes_publicacion']);

        $rankingActual = RankingMensual::with('posiciones')
            ->where('anio_desempeno', $periodo['anio_desempeno'])
            ->where('mes_desempeno', $periodo['mes_desempeno'])
            ->first();

        $vendedoresActivos = Vendedor::where('activo', true)
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $nombreMesDesempeno = RankingMensual::NOMBRES_MESES[$periodo['mes_desempeno']] ?? $periodo['mes_desempeno'];
        $nombreMesPublicacion = RankingMensual::NOMBRES_MESES[$periodo['mes_publicacion']] ?? $periodo['mes_publicacion'];

        return view('livewire.admin.gestion-ranking-actual', [
            'periodo' => $periodo,
            'dentroDePlazo' => $dentroDePlazo,
            'rankingActual' => $rankingActual,
            'vendedoresActivos' => $vendedoresActivos,
            'etiquetaDesempeno' => "{$nombreMesDesempeno} {$periodo['anio_desempeno']}",
            'etiquetaPublicacion' => "{$nombreMesPublicacion} {$periodo['anio_publicacion']}",
            'ahoraLima' => now('America/Lima'),
        ]);
    }
}
