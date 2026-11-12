<?php

use App\Livewire\Admin\GestionContacto;
use App\Livewire\Admin\GestionOficinas;
use App\Livewire\Admin\GestionPortada;
use App\Livewire\Admin\GestionPropiedades;
use App\Livewire\Admin\GestionRankingActual;
use App\Livewire\Admin\GestionUsuarios;
use App\Livewire\Admin\GestionVendedores;
use App\Livewire\Admin\HistorialRankings;
use App\Livewire\Autenticacion\IniciarSesion;
use App\Livewire\Publico\PaginaContacto;
use App\Livewire\Publico\PaginaEmpresa;
use App\Livewire\Publico\PaginaInicio;
use App\Livewire\Publico\PaginaOficinas;
use App\Livewire\Publico\PaginaPropiedades;
use App\Livewire\Publico\PaginaPropiedadesOficina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Sitio Público
Route::get('/', PaginaInicio::class)->name('inicio');
Route::get('/empresa', PaginaEmpresa::class)->name('empresa');
Route::get('/propiedades', PaginaPropiedades::class)->name('propiedades');
Route::get('/oficinas', PaginaOficinas::class)->name('oficinas');
Route::get('/oficinas/{oficina}', PaginaPropiedadesOficina::class)->name('oficinas.propiedades');
Route::get('/contacto', PaginaContacto::class)->name('contacto');

// Autenticación
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', IniciarSesion::class)->name('login');
});

Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('logout');

// Panel Administrativo
Route::prefix('admin')->middleware(['auth', 'rol:ADMINISTRADOR,MARKETING'])->group(function () {
    Route::redirect('/', '/admin/propiedades');

    // Módulos compartidos entre ADMINISTRADOR y MARKETING
    Route::get('/propiedades', GestionPropiedades::class)->name('admin.propiedades');
    Route::get('/vendedores', GestionVendedores::class)->name('admin.vendedores');
    Route::get('/ranking-actual', GestionRankingActual::class)->name('admin.ranking.actual');

    // Módulos exclusivos del ADMINISTRADOR
    Route::middleware('rol:ADMINISTRADOR')->group(function () {
        Route::get('/portada', GestionPortada::class)->name('admin.portada');
        Route::get('/oficinas', GestionOficinas::class)->name('admin.oficinas');
        Route::get('/historial-rankings', HistorialRankings::class)->name('admin.ranking.historial');
        Route::get('/configuracion-contacto', GestionContacto::class)->name('admin.contacto');
        Route::get('/usuarios', GestionUsuarios::class)->name('admin.usuarios');
    });
});
