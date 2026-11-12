<?php

namespace App\Livewire\Admin;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Gestión de Usuarios y Roles | RE/MAX Zona')]
class GestionUsuarios extends Component
{
    public ?int $usuarioId = null;
    public string $nombre = '';
    public string $correo = '';
    public string $password = '';
    public string $rol = Usuario::ROL_MARKETING;
    public bool $activo = true;

    public ?string $mensajeExito = null;
    public ?string $mensajeError = null;

    public function mount(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403, 'Acceso exclusivo para el Administrador.');
        }
    }

    public function nuevoUsuario(): void
    {
        $this->limpiarFormulario();
    }

    public function editarUsuario(int $id): void
    {
        $usuario = Usuario::findOrFail($id);

        $this->resetValidation();
        $this->mensajeExito = null;
        $this->mensajeError = null;

        $this->usuarioId = $usuario->id;
        $this->nombre = $usuario->nombre;
        $this->correo = $usuario->correo;
        $this->rol = $usuario->rol;
        $this->activo = (bool) $usuario->activo;
        $this->password = '';
    }

    public function guardarUsuario(): void
    {
        if (! Auth::user()?->esAdministrador()) {
            abort(403);
        }

        $this->mensajeExito = null;
        $this->mensajeError = null;

        if ($this->usuarioId === (int) Auth::id()) {
            if (! $this->activo) {
                $this->mensajeError = 'No puedes desactivar tu propia cuenta de Administrador.';
                return;
            }
            if ($this->rol !== Usuario::ROL_ADMINISTRADOR) {
                $this->mensajeError = 'No puedes quitarte a ti mismo el rol de Administrador.';
                return;
            }
        }

        $this->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'correo' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'correo')->ignore($this->usuarioId),
            ],
            'rol' => ['required', 'in:ADMINISTRADOR,MARKETING'],
            'activo' => ['boolean'],
            'password' => $this->usuarioId
                ? ['nullable', 'string', 'min:8']
                : ['required', 'string', 'min:8'],
        ], [
            'correo.unique' => 'Ya existe un usuario registrado con ese correo electrónico.',
            'password.required' => 'Ingresa una contraseña de al menos 8 caracteres.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $datos = [
            'nombre' => trim($this->nombre),
            'correo' => strtolower(trim($this->correo)),
            'rol' => $this->rol,
            'activo' => $this->activo,
        ];

        if (! empty($this->password)) {
            $datos['password'] = $this->password;
        }

        if ($this->usuarioId) {
            $usuario = Usuario::findOrFail($this->usuarioId);
            $usuario->update($datos);
            $this->mensajeExito = 'Usuario actualizado correctamente.';
        } else {
            Usuario::create($datos);
            $this->mensajeExito = 'Usuario creado correctamente.';
        }

        $this->limpiarFormulario();
    }

    public function limpiarFormulario(): void
    {
        $this->resetValidation();
        $this->usuarioId = null;
        $this->nombre = '';
        $this->correo = '';
        $this->password = '';
        $this->rol = Usuario::ROL_MARKETING;
        $this->activo = true;
    }

    public function render()
    {
        $usuarios = Usuario::orderBy('id')->get();

        return view('livewire.admin.gestion-usuarios', [
            'usuarios' => $usuarios,
        ]);
    }
}
