<?php

namespace App\Livewire\Admin;

use App\Models\ConfiguracionPortada;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Portada Principal | RE/MAX Zona')]
class GestionPortada extends Component
{
    use WithFileUploads;

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

        $portada = ConfiguracionPortada::obtenerUnica();
        $this->cargarDatosPortada($portada);
    }

    public function limpiarImagenSeleccionada(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $this->imagenArchivo = null;
        $this->resetErrorBag('imagenArchivo');
    }

    public function guardarPortada(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }

        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->validate([
            'imagenArchivo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'imagenArchivo.image' => 'El archivo seleccionado debe ser una imagen válida.',
            'imagenArchivo.mimes' => 'La fotografía debe estar en formato JPG, JPEG, PNG o WEBP.',
            'imagenArchivo.max' => 'La fotografía no debe superar los 4 MB (4096 KB).',
        ]);

        $portada = ConfiguracionPortada::obtenerUnica();

        if (! $this->imagenArchivo) {
            $this->cargarDatosPortada($portada);
            $this->mensajeExito = 'Se conservó la fotografía actual de la portada principal.';
            return;
        }

        $imagenAnterior = $portada->imagen;
        $rutaImagenNueva = $this->imagenArchivo->store('portadas', 'public');

        try {
            $portada->update([
                'imagen' => $rutaImagenNueva,
            ]);
        } catch (Throwable $e) {
            if ($rutaImagenNueva && Storage::disk('public')->exists($rutaImagenNueva)) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            throw $e;
        }

        if (
            $imagenAnterior
            && $imagenAnterior !== $rutaImagenNueva
            && ! str_starts_with($imagenAnterior, 'images/')
            && Storage::disk('public')->exists($imagenAnterior)
        ) {
            Storage::disk('public')->delete($imagenAnterior);
        }

        $portada->refresh();
        $this->cargarDatosPortada($portada);
        $this->mensajeExito = 'Fotografía de la portada principal actualizada correctamente.';
    }

    private function cargarDatosPortada(ConfiguracionPortada $portada): void
    {
        $this->imagenActual = $portada->imagen;
        $this->urlImagenActual = $portada->url_imagen;
        $this->imagenArchivo = null;
    }

    public function render()
    {
        return view('livewire.admin.gestion-portada');
    }
}
