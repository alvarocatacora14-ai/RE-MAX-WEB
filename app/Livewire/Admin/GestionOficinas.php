<?php

namespace App\Livewire\Admin;

use App\Models\Oficina;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Gestión de Oficinas | RE/MAX Zona')]
class GestionOficinas extends Component
{
    use WithFileUploads;

    public ?int $oficinaId = null;
    public string $codigo = '';
    public string $slug = '';
    public string $nombre = '';
    public ?string $direccion = null;
    public ?string $telefono = null;
    public ?string $whatsapp = null;
    public ?string $correo = null;
    public ?string $horario = null;
    public ?string $enlace_google_maps = null;
    public $imagenArchivo = null;
    public ?string $imagenActual = null;
    public ?string $urlImagenActual = null;

    public ?string $mensajeExito = null;
    public ?string $mensajeError = null;

    public function mount(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $primeraOficina = Oficina::obtenerTodas()->first();
        if ($primeraOficina) {
            $this->cargarDatosOficina($primeraOficina);
        }
    }

    public function seleccionarOficina(int $id): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $oficina = Oficina::findOrFail($id);

        $this->resetValidation();
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->cargarDatosOficina($oficina);
    }

    public function guardarOficina(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $this->mensajeExito = null;
        $this->mensajeError = null;

        if (! $this->oficinaId) {
            $this->mensajeError = 'Selecciona una de las cuatro oficinas para editar su información.';
            return;
        }

        $oficina = Oficina::findOrFail($this->oficinaId);

        $this->validate([
            'nombre' => ['required', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'regex:/^[0-9]{8,20}$/'],
            'correo' => ['nullable', 'email', 'max:150'],
            'horario' => ['nullable', 'string', 'max:180'],
            'enlace_google_maps' => ['nullable', 'url', 'starts_with:http://,https://', 'max:500'],
            'imagenArchivo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'nombre.required' => 'El nombre de la oficina es obligatorio.',
            'nombre.max' => 'El nombre de la oficina no debe superar los 80 caracteres.',
            'direccion.max' => 'La dirección no debe superar los 255 caracteres.',
            'telefono.max' => 'El teléfono no debe superar los 30 caracteres.',
            'whatsapp.regex' => 'Ingresa solo números con código de país o celular (de 8 a 20 dígitos, ej. 51999999999).',
            'correo.email' => 'Ingresa una dirección de correo electrónico válida.',
            'correo.max' => 'El correo electrónico no debe superar los 150 caracteres.',
            'horario.max' => 'El horario no debe superar los 180 caracteres.',
            'enlace_google_maps.url' => 'Ingresa un enlace válido de Google Maps que inicie con https://',
            'enlace_google_maps.starts_with' => 'El enlace de Google Maps debe iniciar con http:// o https://',
            'enlace_google_maps.max' => 'El enlace de Google Maps no debe superar los 500 caracteres.',
            'imagenArchivo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'imagenArchivo.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'imagenArchivo.max' => 'La fotografía no debe superar los 4 MB.',
        ]);

        $rutaImagenNueva = null;
        $imagenAnterior = $oficina->imagen;
        $rutaImagenFinal = $imagenAnterior;

        if ($this->imagenArchivo) {
            $rutaImagenNueva = $this->imagenArchivo->store('oficinas', 'public');
            $rutaImagenFinal = $rutaImagenNueva;
        }

        try {
            $oficina->update([
                'nombre' => trim($this->nombre),
                'direccion' => $this->normalizarTextoOpcional($this->direccion),
                'telefono' => $this->normalizarTextoOpcional($this->telefono),
                'whatsapp' => $this->normalizarTextoOpcional($this->whatsapp),
                'correo' => $this->normalizarTextoOpcional($this->correo),
                'horario' => $this->normalizarTextoOpcional($this->horario),
                'enlace_google_maps' => $this->normalizarTextoOpcional($this->enlace_google_maps),
                'imagen' => $rutaImagenFinal,
            ]);
        } catch (Throwable $e) {
            if ($rutaImagenNueva && Storage::disk('public')->exists($rutaImagenNueva)) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            throw $e;
        }

        if (
            $rutaImagenNueva
            && $imagenAnterior
            && $imagenAnterior !== $rutaImagenNueva
            && ! str_starts_with($imagenAnterior, 'images/')
            && Storage::disk('public')->exists($imagenAnterior)
        ) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        $oficina->refresh();
        $this->cargarDatosOficina($oficina);
        $this->mensajeExito = "Información de la oficina {$oficina->nombre} actualizada correctamente.";
    }

    private function cargarDatosOficina(Oficina $oficina): void
    {
        $this->oficinaId = $oficina->id;
        $this->codigo = $oficina->codigo;
        $this->slug = $oficina->slug;
        $this->nombre = $oficina->nombre;
        $this->direccion = $oficina->direccion ?? '';
        $this->telefono = $oficina->telefono ?? '';
        $this->whatsapp = $oficina->whatsapp ?? '';
        $this->correo = $oficina->correo ?? '';
        $this->horario = $oficina->horario ?? '';
        $this->enlace_google_maps = $oficina->enlace_google_maps ?? '';
        $this->imagenActual = $oficina->imagen;
        $this->urlImagenActual = $oficina->url_imagen;
        $this->imagenArchivo = null;
    }

    private function normalizarTextoOpcional(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $limpio = trim($valor);

        return $limpio !== '' ? $limpio : null;
    }

    public function render()
    {
        $oficinas = Oficina::obtenerTodas();

        return view('livewire.admin.gestion-oficinas', [
            'oficinas' => $oficinas,
        ]);
    }
}
