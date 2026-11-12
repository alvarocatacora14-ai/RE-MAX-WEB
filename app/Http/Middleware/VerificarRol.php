<?php

namespace App\Http\Middleware;

use App\Models\Usuario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Verifica que el usuario autenticado esté activo y posea el rol autorizado.
     * El ADMINISTRADOR tiene acceso completo a todas las rutas del sistema.
     */
    public function handle(Request $request, Closure $next, string ...$rolesPermitidos): Response
    {
        /** @var Usuario|null $usuario */
        $usuario = Auth::user();

        if (! $usuario) {
            return redirect()->route('login');
        }

        if (! $usuario->activo) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'correo' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        // El ADMINISTRADOR siempre tiene control completo del sistema
        if ($usuario->esAdministrador()) {
            return $next($request);
        }

        if (! empty($rolesPermitidos) && ! in_array($usuario->rol, $rolesPermitidos, true)) {
            abort(403, 'No tienes permisos para acceder a este módulo.');
        }

        return $next($request);
    }
}
