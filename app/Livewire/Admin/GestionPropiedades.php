<?php

namespace App\Livewire\Admin;

use App\Models\Propiedad;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Gestión de Propiedades | RE/MAX Zona')]
class GestionPropiedades extends Component
{
    use WithFileUploads;

    public string $busqueda = '';
    public bool $mostrarFormulario = false;

    public ?int $propiedadId = null;
    public string $nombre = '';
    public string $ubicacion = '';
    public string $oficina = '';
    public ?string $precio = null;
    public ?string $moneda = 'USD';
    public string $enlace_remax = '';
    public bool $activo = true;
    public $imagenArchivo = null;
    public ?string $imagenActual = null;
    public ?string $urlImagenActual = null;

    public ?string $mensajeExito = null;
    public ?string $mensajeError = null;

    public function nuevaPropiedad(): void
    {
        $this->limpiarFormulario();
        $this->mostrarFormulario = true;
    }

    public function editarPropiedad(int $id): void
    {
        $propiedad = Propiedad::findOrFail($id);

        $this->resetValidation();
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->propiedadId = $propiedad->id;
        $this->nombre = $propiedad->nombre;
        $this->ubicacion = $propiedad->ubicacion;
        $this->oficina = $propiedad->oficina ?? '';
        $this->precio = $propiedad->precio !== null ? (string) $propiedad->precio : '';
        $this->moneda = $propiedad->moneda ?? 'USD';
        $this->enlace_remax = $propiedad->enlace_remax;
        $this->activo = (bool) $propiedad->activo;
        $this->imagenActual = $propiedad->imagen;
        $this->urlImagenActual = $propiedad->url_imagen;
        $this->imagenArchivo = null;
        $this->mostrarFormulario = true;
    }

    public function limpiarImagenSeleccionada(): void
    {
        $this->imagenArchivo = null;
        $this->resetErrorBag('imagenArchivo');
    }

    public function guardarPropiedad(): void
    {
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $precioNormalizado = is_string($this->precio) ? trim($this->precio) : $this->precio;
        $tienePrecio = $precioNormalizado !== null && $precioNormalizado !== '';
        $this->precio = $tienePrecio ? (string) $precioNormalizado : null;

        $reglas = [
            'nombre' => ['required', 'string', 'max:140'],
            'ubicacion' => ['required', 'string', 'max:140'],
            'oficina' => ['required', 'string', 'in:' . implode(',', array_keys(Propiedad::OFICINAS))],
            'precio' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'moneda' => $tienePrecio ? ['required', 'in:PEN,USD'] : ['nullable'],
            'enlace_remax' => ['required', 'url', 'starts_with:http://,https://', 'max:500'],
            'activo' => ['boolean'],
            'imagenArchivo' => $this->propiedadId
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
                : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];

        $this->validate($reglas, [
            'nombre.required' => 'El nombre de la propiedad es obligatorio.',
            'ubicacion.required' => 'La ubicación es obligatoria.',
            'oficina.required' => 'Debes seleccionar la oficina responsable.',
            'oficina.in' => 'La oficina responsable seleccionada no es válida.',
            'precio.numeric' => 'El precio debe ser un valor numérico válido.',
            'precio.min' => 'El precio no puede ser negativo.',
            'moneda.required' => 'Debes seleccionar la moneda (PEN o USD) cuando registras un precio.',
            'moneda.in' => 'La moneda debe ser Soles (PEN) o Dólares (USD).',
            'enlace_remax.required' => 'El enlace de RE/MAX Perú es obligatorio.',
            'enlace_remax.url' => 'Ingresa un enlace válido que inicie con https://',
            'imagenArchivo.required' => 'Debes seleccionar una fotografía para la propiedad.',
            'imagenArchivo.image' => 'El archivo seleccionado debe ser una imagen.',
            'imagenArchivo.max' => 'La imagen no debe superar los 4 MB.',
        ]);

        $rutaImagenNueva = null;
        $rutaImagen = $this->imagenActual;
        $imagenAnteriorParaBorrar = null;

        if ($this->imagenArchivo) {
            $rutaImagenNueva = $this->imagenArchivo->store('propiedades', 'public');
            $rutaImagen = $rutaImagenNueva;
            if ($this->propiedadId && $this->imagenActual && $this->imagenActual !== $rutaImagen) {
                $imagenAnteriorParaBorrar = $this->imagenActual;
            }
        }

        $datos = [
            'nombre' => trim($this->nombre),
            'ubicacion' => trim($this->ubicacion),
            'oficina' => $this->oficina,
            'precio' => $tienePrecio ? $this->precio : null,
            'moneda' => $tienePrecio ? $this->moneda : null,
            'enlace_remax' => trim($this->enlace_remax),
            'imagen' => $rutaImagen,
            'activo' => $this->activo,
        ];

        try {
            if ($this->propiedadId) {
                $propiedad = Propiedad::findOrFail($this->propiedadId);
                $propiedad->update($datos);
                $this->mensajeExito = 'Propiedad actualizada correctamente.';
            } else {
                Propiedad::create($datos);
                $this->mensajeExito = 'Propiedad registrada correctamente.';
            }
        } catch (Throwable $e) {
            if ($rutaImagenNueva && Storage::disk('public')->exists($rutaImagenNueva)) {
                Storage::disk('public')->delete($rutaImagenNueva);
            }

            throw $e;
        }

        if ($imagenAnteriorParaBorrar && Storage::disk('public')->exists($imagenAnteriorParaBorrar)) {
            Storage::disk('public')->delete($imagenAnteriorParaBorrar);
        }

        $this->limpiarFormulario();
    }

    public function alternarDestacada(int $id): void
    {
        $propiedad = Propiedad::findOrFail($id);

        $propiedad->update([
            'destacada' => ! $propiedad->destacada,
        ]);
    }

    public function eliminarPropiedad(int $id): void
    {
        $propiedad = Propiedad::findOrFail($id);
        $imagen = $propiedad->imagen;

        $propiedad->delete();

        if ($imagen && Storage::disk('public')->exists($imagen)) {
            Storage::disk('public')->delete($imagen);
        }

        if ($this->propiedadId === $id) {
            $this->limpiarFormulario();
        }

        $this->mensajeExito = 'Propiedad eliminada correctamente.';
    }

    public function limpiarFormulario(): void
    {
        $this->resetValidation();
        $this->propiedadId = null;
        $this->nombre = '';
        $this->ubicacion = '';
        $this->oficina = '';
        $this->precio = null;
        $this->moneda = 'USD';
        $this->enlace_remax = '';
        $this->activo = true;
        $this->imagenArchivo = null;
        $this->imagenActual = null;
        $this->urlImagenActual = null;
        $this->mostrarFormulario = false;
    }

    public function render()
    {
        $propiedades = Propiedad::query()
            ->when($this->busqueda !== '', function ($query) {
                $termino = '%' . trim($this->busqueda) . '%';
                $query->where(function ($q) use ($termino) {
                    $q->where('nombre', 'like', $termino)
                        ->orWhere('ubicacion', 'like', $termino);
                });
            })
            ->orderByDesc('id')
            ->get();

        return view('livewire.admin.gestion-propiedades', [
            'propiedades' => $propiedades,
        ]);
    }
}
