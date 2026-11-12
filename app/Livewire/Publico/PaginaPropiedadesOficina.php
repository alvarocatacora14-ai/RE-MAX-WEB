<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use App\Models\Oficina;
use App\Models\Propiedad;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.publico')]
class PaginaPropiedadesOficina extends Component
{
    public string $slugOficina = '';
    public string $codigoOficina = '';
    public string $nombreOficina = '';

    #[Url(except: '')]
    public string $busqueda = '';

    #[Url(except: '')]
    public string $ubicacion = '';

    #[Url(except: '')]
    public string $moneda = '';

    public function mount(string $oficina): void
    {
        $slug = strtolower(trim($oficina));

        if (! isset(Propiedad::SLUGS_OFICINAS[$slug])) {
            abort(404);
        }

        $this->slugOficina = $slug;
        $this->codigoOficina = Propiedad::SLUGS_OFICINAS[$slug];
        $this->nombreOficina = Propiedad::OFICINAS[$this->codigoOficina];
    }

    public function limpiarFiltros(): void
    {
        $this->reset(['busqueda', 'ubicacion', 'moneda']);
    }

    public function render()
    {
        $consultaBase = Propiedad::query()
            ->where('activo', true)
            ->where('oficina', $this->codigoOficina);

        $totalActivasOficina = (clone $consultaBase)->count();

        $consulta = clone $consultaBase;

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

        $ubicacionesDisponibles = (clone $consultaBase)
            ->whereNotNull('ubicacion')
            ->where('ubicacion', '!=', '')
            ->distinct()
            ->orderBy('ubicacion')
            ->pluck('ubicacion');

        $sede = Oficina::porSlug($this->slugOficina);
        if ($sede) {
            $this->nombreOficina = $sede->nombre;
        }

        $contacto = ConfiguracionContacto::obtenerUnica();

        return view('livewire.publico.pagina-propiedades-oficina', [
            'propiedades' => $propiedades,
            'ubicacionesDisponibles' => $ubicacionesDisponibles,
            'totalActivasOficina' => $totalActivasOficina,
            'contacto' => $contacto,
            'sede' => $sede,
        ])->title('Oficina ' . $this->nombreOficina . ' | Propiedades | RE/MAX Zona');
    }
}
