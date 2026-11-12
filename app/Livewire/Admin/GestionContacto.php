<?php

namespace App\Livewire\Admin;

use App\Models\ConfiguracionContacto;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Configuración de Contacto | RE/MAX Zona')]
class GestionContacto extends Component
{
    public string $nombre_empresa = '';
    public string $telefono_visible = '';
    public string $whatsapp_general = '';
    public string $correo_contacto = '';
    public ?string $direccion = null;
    public string $horario_lunes_viernes = '';
    public string $horario_sabados = '';
    public string $whatsapp_postulacion = '';

    public ?string $mensajeExito = null;

    public function mount(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $config = ConfiguracionContacto::obtenerUnica();

        $this->nombre_empresa = $config->nombre_empresa;
        $this->telefono_visible = $config->telefono_visible;
        $this->whatsapp_general = $config->whatsapp_general;
        $this->correo_contacto = $config->correo_contacto;
        $this->direccion = $config->direccion;
        $this->horario_lunes_viernes = $config->horario_lunes_viernes;
        $this->horario_sabados = $config->horario_sabados;
        $this->whatsapp_postulacion = $config->whatsapp_postulacion;
    }

    public function guardarConfiguracion(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403);
        }

        $this->mensajeExito = null;

        $this->validate([
            'nombre_empresa' => ['required', 'string', 'max:120'],
            'telefono_visible' => ['required', 'string', 'max:30'],
            'whatsapp_general' => ['required', 'string', 'regex:/^[0-9]{8,20}$/'],
            'correo_contacto' => ['required', 'email', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'horario_lunes_viernes' => ['required', 'string', 'max:120'],
            'horario_sabados' => ['required', 'string', 'max:120'],
            'whatsapp_postulacion' => ['required', 'string', 'regex:/^[0-9]{8,20}$/'],
        ], [
            'whatsapp_general.regex' => 'Ingresa solo números con código de país (ej. 51999999999).',
            'whatsapp_postulacion.regex' => 'Ingresa solo números con código de país (ej. 51999999999).',
        ]);

        $config = ConfiguracionContacto::obtenerUnica();
        $config->update([
            'nombre_empresa' => trim($this->nombre_empresa),
            'telefono_visible' => trim($this->telefono_visible),
            'whatsapp_general' => trim($this->whatsapp_general),
            'correo_contacto' => trim($this->correo_contacto),
            'direccion' => $this->direccion ? trim($this->direccion) : null,
            'horario_lunes_viernes' => trim($this->horario_lunes_viernes),
            'horario_sabados' => trim($this->horario_sabados),
            'whatsapp_postulacion' => trim($this->whatsapp_postulacion),
        ]);

        $this->mensajeExito = 'Información global de contacto actualizada correctamente.';
    }

    public function render()
    {
        return view('livewire.admin.gestion-contacto');
    }
}
