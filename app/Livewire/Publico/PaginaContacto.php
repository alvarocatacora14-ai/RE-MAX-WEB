<?php

namespace App\Livewire\Publico;

use App\Models\ConfiguracionContacto;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.publico')]
#[Title('Contacto | RE/MAX Zona')]
class PaginaContacto extends Component
{
    public string $nombre = '';

    public string $telefono = '';

    public string $correo = '';

    public string $asunto = 'Consulta sobre propiedades';

    public string $mensaje = '';

    public bool $consultaPreparada = false;

    public string $enlaceWhatsappConsulta = '';

    public string $enlaceCorreoConsulta = '';

    protected function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:3', 'max:120'],
            'telefono' => ['required', 'string', 'min:6', 'max:30'],
            'correo' => ['nullable', 'email', 'max:120'],
            'asunto' => [
                'required',
                'string',
                'in:Consulta sobre propiedades,Venta o alquiler de inmueble,Inversión inmobiliaria,Postulación / Trabaja con nosotros,Otra consulta',
            ],
            'mensaje' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nombre.required' => 'Por favor, ingresa tu nombre completo.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'telefono.required' => 'Por favor, ingresa un número de teléfono o celular.',
            'telefono.min' => 'El teléfono debe tener al menos 6 caracteres.',
            'correo.email' => 'Ingresa una dirección de correo electrónico válida.',
            'asunto.required' => 'Selecciona un motivo de consulta.',
            'asunto.in' => 'Selecciona un motivo de consulta válido.',
            'mensaje.required' => 'Por favor, escribe el detalle de tu consulta.',
            'mensaje.min' => 'El mensaje debe contener al menos 10 caracteres.',
        ];
    }

    public function updated($propiedad): void
    {
        if (in_array($propiedad, ['nombre', 'telefono', 'correo', 'asunto', 'mensaje'], true)) {
            $this->consultaPreparada = false;
        }
    }

    public function prepararConsulta(): void
    {
        $datos = $this->validate();

        $contacto = ConfiguracionContacto::obtenerUnica();

        $lineas = [
            "Hola {$contacto->nombre_empresa}, deseo realizar una consulta:",
            "Nombre: " . trim($datos['nombre']),
            "Teléfono: " . trim($datos['telefono']),
        ];

        if (! empty($datos['correo'])) {
            $lineas[] = "Correo: " . trim($datos['correo']);
        }

        $lineas[] = "Motivo: " . $datos['asunto'];
        $lineas[] = "Mensaje: " . trim($datos['mensaje']);

        $textoMensaje = implode("\n", $lineas);

        $numeroWhatsapp = ($datos['asunto'] === 'Postulación / Trabaja con nosotros' && ! empty($contacto->whatsapp_postulacion))
            ? $contacto->whatsapp_postulacion
            : $contacto->whatsapp_general;

        $this->enlaceWhatsappConsulta = 'https://api.whatsapp.com/send/?phone='
            . rawurlencode((string) $numeroWhatsapp)
            . '&text='
            . rawurlencode($textoMensaje);

        $asuntoCorreo = $datos['asunto'] . ' - ' . trim($datos['nombre']);
        $this->enlaceCorreoConsulta = 'mailto:' . $contacto->correo_contacto
            . '?subject=' . rawurlencode($asuntoCorreo)
            . '&body=' . rawurlencode($textoMensaje);

        $this->consultaPreparada = true;
    }

    public function limpiarFormulario(): void
    {
        $this->reset([
            'nombre',
            'telefono',
            'correo',
            'asunto',
            'mensaje',
            'consultaPreparada',
            'enlaceWhatsappConsulta',
            'enlaceCorreoConsulta',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $contacto = ConfiguracionContacto::obtenerUnica();

        return view('livewire.publico.pagina-contacto', [
            'contacto' => $contacto,
        ]);
    }
}
