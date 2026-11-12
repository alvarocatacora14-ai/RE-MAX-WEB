<?php

namespace App\Livewire\Autenticacion;

use App\Models\Usuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Iniciar sesión | RE/MAX Zona')]
class IniciarSesion extends Component
{
    public string $correo = '';
    public string $password = '';
    public bool $recordar = false;

    public function autenticar(): void
    {
        $this->validate([
            'correo' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ], [
            'correo.required' => 'Ingresa tu correo electrónico.',
            'correo.email' => 'Ingresa un correo electrónico válido.',
            'password.required' => 'Ingresa tu contraseña.',
        ]);

        $claveIntento = Str::lower($this->correo) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($claveIntento, 5)) {
            $segundos = RateLimiter::availableIn($claveIntento);
            $this->addError('correo', "Demasiados intentos. Intenta nuevamente en {$segundos} segundos.");
            return;
        }

        /** @var Usuario|null $usuario */
        $usuario = Usuario::where('correo', $this->correo)->first();

        if (! $usuario || ! Hash::check($this->password, $usuario->password)) {
            RateLimiter::hit($claveIntento, 60);
            $this->addError('correo', 'Las credenciales ingresadas no son válidas.');
            return;
        }

        if (! $usuario->activo) {
            $this->addError('correo', 'Esta cuenta se encuentra desactivada. Contacta al administrador.');
            return;
        }

        RateLimiter::clear($claveIntento);
        Auth::login($usuario, $this->recordar);
        session()->regenerate();

        $this->redirectRoute('admin.propiedades');
    }

    public function render()
    {
        return view('livewire.autenticacion.iniciar-sesion');
    }
}
