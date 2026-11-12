<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use App\Models\Oficina;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.publico')]
#[Title('Oficinas | RE/MAX Zona')]
class PaginaOficinas extends Component
{
    public function render()
    {
        $contacto = ConfiguracionContacto::obtenerUnica();
        $oficinas = Oficina::obtenerTodas();

        return view('livewire.publico.pagina-oficinas', [
            'contacto' => $contacto,
            'oficinas' => $oficinas,
        ]);
    }
}
