<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $title ?? 'Panel Administrativo | RE/MAX Zona' }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @livewireStyles
</head>
<body>
    @auth
        <section class="dashboard">
            <aside class="sidebar">
                <a class="sidebar-brand" href="{{ route('inicio') }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ asset('images/CONTENEDOR.png') }}" alt="RE/MAX Zona">
                    <span>Zona Inmobiliaria</span>
                </a>

                <nav class="sidebar-nav" aria-label="Secciones de administración">
                    <a href="{{ route('inicio') }}" target="_blank" rel="noopener noreferrer">
                        <span aria-hidden="true">⌂</span> Ver sitio público
                    </a>

                    <a href="{{ route('admin.propiedades') }}" class="{{ request()->routeIs('admin.propiedades') ? 'is-active' : '' }}">
                        <span aria-hidden="true">▦</span> Propiedades
                    </a>

                    <a href="{{ route('admin.vendedores') }}" class="{{ request()->routeIs('admin.vendedores') ? 'is-active' : '' }}">
                        <span aria-hidden="true">♟</span> Vendedores
                    </a>

                    <a href="{{ route('admin.ranking.actual') }}" class="{{ request()->routeIs('admin.ranking.actual') ? 'is-active' : '' }}">
                        <span aria-hidden="true">★</span> Ranking Mensual
                    </a>

                    @if(auth()->user()->esAdministrador())
                        <a href="{{ route('admin.portada') }}" class="{{ request()->routeIs('admin.portada') ? 'is-active' : '' }}">
                            <span aria-hidden="true">🖼</span> Portada principal
                        </a>

                        <a href="{{ route('admin.oficinas') }}" class="{{ request()->routeIs('admin.oficinas') ? 'is-active' : '' }}">
                            <span aria-hidden="true">🏢</span> Oficinas
                        </a>

                        <a href="{{ route('admin.ranking.historial') }}" class="{{ request()->routeIs('admin.ranking.historial') ? 'is-active' : '' }}">
                            <span aria-hidden="true">⌚</span> Historial de Rankings
                        </a>

                        <a href="{{ route('admin.contacto') }}" class="{{ request()->routeIs('admin.contacto') ? 'is-active' : '' }}">
                            <span aria-hidden="true">☎</span> Contacto Global
                        </a>

                        <a href="{{ route('admin.usuarios') }}" class="{{ request()->routeIs('admin.usuarios') ? 'is-active' : '' }}">
                            <span aria-hidden="true">⚙</span> Usuarios y Roles
                        </a>
                    @endif
                </nav>

                <div class="sidebar-footer">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="sign-out" type="submit">
                            <span aria-hidden="true">↪</span> Cerrar sesión
                        </button>
                    </form>
                </div>
            </aside>

            <div class="dashboard-content">
                <header class="topbar">
                    <div>
                        <strong>Panel de Administración</strong>
                    </div>
                    <div class="topbar-user">
                        <span class="role-badge">{{ auth()->user()->rol }}</span>
                        <span class="user-avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}</span>
                        <span>{{ auth()->user()->nombre }}</span>
                    </div>
                </header>

                <main class="workspace">
                    {{ $slot }}
                </main>
            </div>
        </section>
    @else
        {{ $slot }}
    @endauth

    @livewireScripts
</body>
</html>
