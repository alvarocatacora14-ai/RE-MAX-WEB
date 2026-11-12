<div>
    <main id="contenido">
        <section class="page-banner" aria-labelledby="oficinas-hero-title">
            <div class="container">
                <p class="eyebrow">Cobertura nacional</p>
                <h1 id="oficinas-hero-title">Nuestras oficinas</h1>
                <p>Presencia regional para brindarte atención inmobiliaria personalizada.</p>
            </div>
        </section>

        <section class="section section--muted" id="oficinas" aria-labelledby="offices-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">{{ $contacto->nombre_empresa }}</p>
                    <h2 id="offices-title">Sedes de atención</h2>
                </div>
                <div class="card-grid card-grid--offices">
                    @foreach($oficinas as $sede)
                        <article class="card office-card" wire:key="sede-publica-{{ $sede->id }}">
                            @if($sede->url_imagen)
                                <img src="{{ $sede->url_imagen }}" alt="Oficina {{ $contacto->nombre_empresa }} {{ $sede->nombre }}">
                            @else
                                <div class="office-card__placeholder" role="img" aria-label="Oficina {{ $contacto->nombre_empresa }} {{ $sede->nombre }} — Espacio institucional">
                                    <span class="office-card__placeholder-eyebrow">{{ $contacto->nombre_empresa }}</span>
                                    <strong class="office-card__placeholder-title">Sede {{ $sede->nombre }}</strong>
                                </div>
                            @endif
                            <div class="office-card__body">
                                <h3>{{ $sede->nombre }}</h3>

                                @if($sede->tiene_datos_contacto)
                                    <div class="office-card__details">
                                        @if($sede->direccion)
                                            <p><strong>Dirección:</strong> {{ $sede->direccion }}</p>
                                        @endif
                                        @if($sede->telefono)
                                            <p>
                                                <strong>Teléfono:</strong>
                                                @if($sede->telefono_enlace)
                                                    <a href="{{ $sede->telefono_enlace }}">{{ $sede->telefono }}</a>
                                                @else
                                                    {{ $sede->telefono }}
                                                @endif
                                            </p>
                                        @endif
                                        @if($sede->whatsapp && $sede->whatsapp_enlace)
                                            <p>
                                                <strong>WhatsApp:</strong>
                                                <a href="{{ $sede->whatsapp_enlace }}" target="_blank" rel="noopener noreferrer">{{ $sede->whatsapp }}</a>
                                            </p>
                                        @endif
                                        @if($sede->correo)
                                            <p>
                                                <strong>Correo:</strong>
                                                <a href="mailto:{{ $sede->correo }}">{{ $sede->correo }}</a>
                                            </p>
                                        @endif
                                        @if($sede->horario)
                                            <p><strong>Horario:</strong> {{ $sede->horario }}</p>
                                        @endif
                                        @if($sede->enlace_google_maps)
                                            <p>
                                                <a href="{{ $sede->enlace_google_maps }}" target="_blank" rel="noopener noreferrer">
                                                    Ver ubicación en Google Maps ↗
                                                </a>
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <p>Atención inmobiliaria personalizada.</p>
                                @endif

                                <a class="button button--primary" href="{{ route('oficinas.propiedades', ['oficina' => $sede->slug]) }}">
                                    Ver propiedades
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section" aria-labelledby="offices-info-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Información de atención centralizada</p>
                    <h2 id="offices-info-title">Canales y horarios de contacto general de {{ $contacto->nombre_empresa }}</h2>
                    <p class="section-subtitle">
                        Los teléfonos, correos, cobertura y horarios disponibles corresponden al contacto general de {{ $contacto->nombre_empresa }} y no a datos individuales de cada sede.
                    </p>
                </div>

                <div class="card-grid card-grid--info">
                    <article class="card info-card">
                        <h3>Cobertura y ubicación</h3>
                        <p>{{ $contacto->direccion ?: 'Pendiente de configuración' }}</p>
                    </article>

                    <article class="card info-card">
                        <h3>Atención telefónica y correo</h3>
                        @if($contacto->telefono_visible)
                            <p>
                                <strong>Teléfono:</strong>
                                <a href="tel:{{ $contacto->telefono_enlace }}">{{ $contacto->telefono_visible }}</a>
                            </p>
                        @else
                            <p><strong>Teléfono:</strong> Pendiente de configuración</p>
                        @endif

                        @if($contacto->correo_contacto)
                            <p>
                                <strong>Correo:</strong>
                                <a href="mailto:{{ $contacto->correo_contacto }}">{{ $contacto->correo_contacto }}</a>
                            </p>
                        @else
                            <p><strong>Correo:</strong> Pendiente de configuración</p>
                        @endif
                    </article>

                    <article class="card info-card">
                        <h3>Horario de atención</h3>
                        <p>{{ $contacto->horario_lunes_viernes ?: 'Pendiente de configuración' }}</p>
                        <p>{{ $contacto->horario_sabados ?: 'Pendiente de configuración' }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="cta" aria-labelledby="oficinas-cta-title">
            <div class="container">
                <h2 id="oficinas-cta-title">Comunícate con nuestras oficinas</h2>
                <p>Contáctanos para coordinar una cita o recibir asesoría de nuestro equipo inmobiliario.</p>
                <a
                    class="button button--accent"
                    href="https://wa.me/{{ $contacto->whatsapp_general }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Contactar por WhatsApp
                </a>
            </div>
        </section>
    </main>
</div>
