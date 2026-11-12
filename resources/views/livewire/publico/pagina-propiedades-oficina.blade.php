<div>
    <main id="contenido">
        <section class="page-banner" aria-labelledby="oficina-propiedades-hero-title">
            <div class="container">
                <p class="eyebrow">Oficina {{ $nombreOficina }}</p>
                <h1 id="oficina-propiedades-hero-title">Propiedades en {{ $nombreOficina }}</h1>
                <p>Explora el catálogo de inmuebles administrados por nuestra sede de {{ $nombreOficina }}.</p>
            </div>
        </section>

        <section class="section section--muted" id="propiedades" aria-labelledby="office-properties-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Catálogo por sede</p>
                    <h2 id="office-properties-title">Oficina {{ $nombreOficina }}</h2>
                    <div class="office-catalog-nav" aria-label="Navegación entre oficinas">
                        <a class="button button--outline" href="{{ route('oficinas') }}">
                            ← Todas las oficinas
                        </a>
                        <a class="button button--outline" href="{{ route('propiedades') }}">
                            Ver catálogo general
                        </a>
                    </div>
                </div>

                @if(isset($sede) && $sede->tiene_datos_contacto)
                    <div class="card office-contact-summary" aria-label="Datos de contacto de la oficina {{ $nombreOficina }}">
                        @if($sede->direccion)
                            <div>
                                <strong>Dirección:</strong>
                                <span>{{ $sede->direccion }}</span>
                            </div>
                        @endif
                        @if($sede->telefono)
                            <div>
                                <strong>Teléfono:</strong>
                                @if($sede->telefono_enlace)
                                    <a href="{{ $sede->telefono_enlace }}">{{ $sede->telefono }}</a>
                                @else
                                    <span>{{ $sede->telefono }}</span>
                                @endif
                            </div>
                        @endif
                        @if($sede->whatsapp && $sede->whatsapp_enlace)
                            <div>
                                <strong>WhatsApp:</strong>
                                <a href="{{ $sede->whatsapp_enlace }}" target="_blank" rel="noopener noreferrer">{{ $sede->whatsapp }}</a>
                            </div>
                        @endif
                        @if($sede->correo)
                            <div>
                                <strong>Correo:</strong>
                                <a href="mailto:{{ $sede->correo }}">{{ $sede->correo }}</a>
                            </div>
                        @endif
                        @if($sede->horario)
                            <div>
                                <strong>Horario:</strong>
                                <span>{{ $sede->horario }}</span>
                            </div>
                        @endif
                        @if($sede->enlace_google_maps)
                            <div>
                                <a href="{{ $sede->enlace_google_maps }}" target="_blank" rel="noopener noreferrer">
                                    Ver en Google Maps ↗
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="filter-bar" role="search" aria-label="Filtrar propiedades de la oficina {{ $nombreOficina }}">
                    <div class="filter-group">
                        <label for="filtro-busqueda">Buscar propiedad o zona</label>
                        <input
                            id="filtro-busqueda"
                            type="search"
                            wire:model.live.debounce.300ms="busqueda"
                            placeholder="Ej. departamento, casa, {{ $nombreOficina }}..."
                        >
                    </div>

                    <div class="filter-group">
                        <label for="filtro-ubicacion">Ubicación</label>
                        <select id="filtro-ubicacion" wire:model.live="ubicacion">
                            <option value="">Todas las ubicaciones</option>
                            @foreach($ubicacionesDisponibles as $ubicacionItem)
                                <option value="{{ $ubicacionItem }}">{{ $ubicacionItem }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filtro-moneda">Moneda</label>
                        <select id="filtro-moneda" wire:model.live="moneda">
                            <option value="">Todas las monedas</option>
                            <option value="PEN">Soles (S/)</option>
                            <option value="USD">Dólares ($)</option>
                        </select>
                    </div>

                    @if($busqueda !== '' || $ubicacion !== '' || $moneda !== '')
                        <div class="filter-actions">
                            <button type="button" class="button button--outline" wire:click="limpiarFiltros">
                                Limpiar filtros
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-grid card-grid--properties" id="featured-properties">
                    @forelse($propiedades as $propiedad)
                        <article class="card property-card" wire:key="propiedad-oficina-{{ $propiedad->id }}">
                            <img src="{{ $propiedad->url_imagen }}" alt="{{ $propiedad->nombre }}">
                            <div>
                                <h3>{{ $propiedad->nombre }}</h3>
                                <p>{{ $propiedad->ubicacion }}</p>
                                @if($propiedad->precio_formateado)
                                    <p class="property-price">{{ $propiedad->precio_formateado }}</p>
                                @endif
                                <a href="{{ $propiedad->enlace_remax }}" target="_blank" rel="noopener noreferrer">
                                    Más información →
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="empty-state-box">
                            @if($totalActivasOficina > 0)
                                <p>No se encontraron propiedades en la oficina {{ $nombreOficina }} que coincidan con los filtros seleccionados.</p>
                                <button type="button" class="button button--accent" wire:click="limpiarFiltros">
                                    Ver todas las propiedades de {{ $nombreOficina }}
                                </button>
                            @else
                                <p>Actualmente no hay propiedades disponibles asignadas a la oficina de {{ $nombreOficina }}.</p>
                                <div class="empty-state-actions">
                                    <a class="button button--primary" href="{{ route('oficinas') }}">
                                        Volver a Oficinas
                                    </a>
                                    <a class="button button--outline" href="{{ route('propiedades') }}">
                                        Consultar catálogo general
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="cta" aria-labelledby="oficina-propiedades-cta-title">
            <div class="container">
                <h2 id="oficina-propiedades-cta-title">¿Buscas asesoría personalizada?</h2>
                <p>Comunícate con nuestro equipo para recibir información detallada sobre nuestras propiedades disponibles.</p>
                <a
                    class="button button--accent"
                    href="https://wa.me/{{ $contacto->whatsapp_general }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Consultar por WhatsApp
                </a>
            </div>
        </section>
    </main>
</div>
