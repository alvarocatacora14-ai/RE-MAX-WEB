<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="RE/MAX Zona: compra, venta y alquiler de propiedades en el sur del Perú.">
    <title>{{ $title ?? 'RE/MAX Zona | Inmobiliaria' }}</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @livewireStyles
</head>
<body>
    @php
        $contacto = \App\Models\ConfiguracionContacto::obtenerUnica();
    @endphp

    <a class="skip-link" href="#contenido">Saltar al contenido</a>

    <header class="site-header">
        <a class="brand" href="{{ route('inicio') }}" aria-label="RE/MAX Zona, ir al inicio">
            <img src="{{ asset('images/CONTENEDOR.png') }}" alt="RE/MAX Zona">
        </a>
        <nav class="site-nav" aria-label="Navegación principal">
            <a href="{{ route('inicio') }}" @class(['is-active' => request()->routeIs('inicio')])>Inicio</a>
            <a href="{{ route('empresa') }}" @class(['is-active' => request()->routeIs('empresa')])>Empresa</a>
            <a href="{{ route('propiedades') }}" @class(['is-active' => request()->routeIs('propiedades')])>Propiedades</a>
            <a href="{{ route('oficinas') }}" @class(['is-active' => request()->routeIs('oficinas', 'oficinas.*')])>Oficinas</a>
            <a href="{{ route('contacto') }}" @class(['is-active' => request()->routeIs('contacto')])>Contacto</a>
        </nav>
    </header>

    {{ $slot }}

    <footer class="site-footer" id="contacto">
        <div class="container footer-grid">
            <div>
                <h2>{{ $contacto->nombre_empresa }}</h2>
                <p>Inmobiliaria especializada en compra, venta, alquiler e inversión inmobiliaria.</p>
                @if($contacto->direccion)
                    <p>{{ $contacto->direccion }}</p>
                @endif
            </div>
            <div>
                <h3>Contacto</h3>
                <p><a href="tel:{{ $contacto->telefono_enlace }}">{{ $contacto->telefono_visible }}</a></p>
                <p><a href="mailto:{{ $contacto->correo_contacto }}">{{ $contacto->correo_contacto }}</a></p>
                <p><a href="https://wa.me/{{ $contacto->whatsapp_general }}" target="_blank" rel="noopener noreferrer">WhatsApp directo</a></p>
            </div>
            <div>
                <h3>Horario</h3>
                <p>{{ $contacto->horario_lunes_viernes }}</p>
                <p>{{ $contacto->horario_sabados }}</p>
            </div>
        </div>
        <div class="footer-bottom">© {{ now('America/Lima')->year }} {{ $contacto->nombre_empresa }}. Todos los derechos reservados.</div>
    </footer>

    <a
        class="whatsapp"
        href="https://wa.me/{{ $contacto->whatsapp_general }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Contactar por WhatsApp"
    >
        💬<span> WhatsApp</span>
    </a>

    @livewireScripts
</body>
</html>
