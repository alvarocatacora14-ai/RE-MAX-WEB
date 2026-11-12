<?php

namespace App\Livewire\Admin;

use App\Models\Vendedor;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

#[Layout('components.layouts.admin')]
#[Title('Gestión de Vendedores | RE/MAX Zona')]
class GestionVendedores extends Component
{
    use WithFileUploads;

    public string $busqueda = '';

    public ?int $vendedorId = null;
    public string $nombres = '';
    public string $apellidos = '';
    public string $celular = '';
    public bool $activo = true;
    public $fotoArchivo = null;
    public ?string $fotoActual = null;
    public ?string $urlFotoActual = null;

    public ?string $mensajeExito = null;
    public ?string $mensajeError = null;

    public function nuevoVendedor(): void
    {
        $this->limpiarFormulario();
    }

    public function editarVendedor(int $id): void
    {
        $vendedor = Vendedor::findOrFail($id);

        $this->resetValidation();
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->vendedorId = $vendedor->id;
        $this->nombres = $vendedor->nombres;
        $this->apellidos = $vendedor->apellidos;
        $this->celular = $vendedor->celular;
        $this->activo = (bool) $vendedor->activo;
        $this->fotoActual = $vendedor->foto;
        $this->urlFotoActual = $vendedor->url_foto;
        $this->fotoArchivo = null;
    }

    public function limpiarFotoSeleccionada(): void
    {
        $this->fotoArchivo = null;
        $this->resetErrorBag('fotoArchivo');
    }

    public function guardarVendedor(): void
    {
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->validate([
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'celular' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'activo' => ['boolean'],
            'fotoArchivo' => $this->vendedorId
                ? ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
                : ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'nombres.required' => 'Ingresa los nombres del vendedor.',
            'apellidos.required' => 'Ingresa los apellidos del vendedor.',
            'celular.required' => 'Ingresa el número de celular del vendedor.',
            'celular.regex' => 'Ingresa un número de celular válido.',
            'fotoArchivo.required' => 'Selecciona la fotografía del vendedor.',
            'fotoArchivo.image' => 'El archivo seleccionado debe ser una imagen válida.',
        ]);

        $rutaFotoNueva = null;
        $rutaFoto = $this->fotoActual;
        $fotoAnteriorParaBorrar = null;

        if ($this->fotoArchivo) {
            $rutaFotoNueva = $this->fotoArchivo->store('vendedores/actual', 'public');
            $rutaFoto = $rutaFotoNueva;
            if ($this->vendedorId && $this->fotoActual && $this->fotoActual !== $rutaFoto) {
                // Solo se reemplaza el archivo en vendedores/actual/; jamás se tocan las copias históricas en rankings/
                if (str_starts_with($this->fotoActual, 'vendedores/actual/')) {
                    $fotoAnteriorParaBorrar = $this->fotoActual;
                }
            }
        }

        $datos = [
            'nombres' => trim($this->nombres),
            'apellidos' => trim($this->apellidos),
            'celular' => trim($this->celular),
            'foto' => $rutaFoto,
            'activo' => $this->activo,
        ];

        try {
            if ($this->vendedorId) {
                $vendedor = Vendedor::findOrFail($this->vendedorId);
                $vendedor->update($datos);
                $this->mensajeExito = 'Datos del vendedor actualizados correctamente.';
            } else {
                Vendedor::create($datos);
                $this->mensajeExito = 'Vendedor registrado correctamente en el padrón.';
            }
        } catch (Throwable $e) {
            if ($rutaFotoNueva && Storage::disk('public')->exists($rutaFotoNueva)) {
                Storage::disk('public')->delete($rutaFotoNueva);
            }

            throw $e;
        }

        if ($fotoAnteriorParaBorrar && Storage::disk('public')->exists($fotoAnteriorParaBorrar)) {
            Storage::disk('public')->delete($fotoAnteriorParaBorrar);
        }

        $this->limpiarFormulario();
    }

    public function limpiarFormulario(): void
    {
        $this->resetValidation();
        $this->vendedorId = null;
        $this->nombres = '';
        $this->apellidos = '';
        $this->celular = '';
        $this->activo = true;
        $this->fotoArchivo = null;
        $this->fotoActual = null;
        $this->urlFotoActual = null;
    }

    public function render()
    {
        $vendedores = Vendedor::query()
            ->when($this->busqueda !== '', function ($query) {
                $termino = '%' . trim($this->busqueda) . '%';
                $query->where(function ($q) use ($termino) {
                    $q->where('nombres', 'like', $termino)
                        ->orWhere('apellidos', 'like', $termino)
                        ->orWhere('celular', 'like', $termino);
                });
            })
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        return view('livewire.admin.gestion-vendedores', [
            'vendedores' => $vendedores,
        ]);
    }
}
