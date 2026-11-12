<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use App\Models\Propiedad;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.publico')]
#[Title('Propiedades | RE/MAX Zona')]
class PaginaPropiedades extends Component
{
    #[Url(except: '')]
    public string $busqueda = '';

    #[Url(except: '')]
    public string $ubicacion = '';

    #[Url(except: '')]
    public string $moneda = '';

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'ubicacion', 'moneda']);
    }

    public function render()
    {
        $consulta = Propiedad::query()->where('activo', true);

        $termino = trim($this->busqueda);
        if ($termino !== '') {
            $consulta->where(function ($subconsulta) use ($termino) {
                $subconsulta->where('nombre', 'like', '%' . $termino . '%')
                    ->orWhere('ubicacion', 'like', '%' . $termino . '%');
            });
        }

        if ($this->ubicacion !== '') {
            $consulta->where('ubicacion', $this->ubicacion);
        }

        if (in_array($this->moneda, [Propiedad::MONEDA_SOLES, Propiedad::MONEDA_DOLARES], true)) {
            $consulta->where('moneda', $this->moneda);
        }

        $propiedades = $consulta->orderBy('id')->get();

        $ubicacionesDisponibles = Propiedad::where('activo', true)
            ->whereNotNull('ubicacion')
            ->where('ubicacion', '!=', '')
            ->distinct()
            ->orderBy('ubicacion')
            ->pluck('ubicacion');

        $totalActivas = Propiedad::where('activo', true)->count();
        $contacto = ConfiguracionContacto::obtenerUnica();

        return view('livewire.publico.pagina-propiedades', [
            'propiedades' => $propiedades,
            'ubicacionesDisponibles' => $ubicacionesDisponibles,
            'totalActivas' => $totalActivas,
            'contacto' => $contacto,
        ]);
    }
}
