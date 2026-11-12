<div>
    <main id="contenido">
        <section class="page-banner" aria-labelledby="propiedades-hero-title">
            <div class="container">
                <p class="eyebrow">Encuentra tu próximo hogar</p>
                <h1 id="propiedades-hero-title">Propiedades disponibles</h1>
                <p>Compra, vende o alquila con los mejores asesores inmobiliarios del sur del Perú.</p>
            </div>
        </section>

        <section class="section section--muted" id="propiedades" aria-labelledby="properties-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Catálogo inmobiliario</p>
                    <h2 id="properties-title">Consulta nuestras propiedades</h2>
                </div>

                <div class="filter-bar" role="search" aria-label="Filtrar propiedades">
                    <div class="filter-group">
                        <label for="filtro-busqueda">Buscar propiedad o zona</label>
                        <input
                            id="filtro-busqueda"
                            type="search"
                            wire:model.live.debounce.300ms="busqueda"
                            placeholder="Ej. departamento, casa, Arequipa..."
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
                        <article class="card property-card" wire:key="propiedad-{{ $propiedad->id }}">
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
                            @if($totalActivas > 0)
                                <p>No se encontraron propiedades que coincidan con los filtros seleccionados.</p>
                                <button type="button" class="button button--accent" wire:click="limpiarFiltros">
                                    Ver todas las propiedades
                                </button>
                            @else
                                <p>Pronto publicaremos nuevas propiedades destacadas.</p>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="cta" aria-labelledby="propiedades-cta-title">
            <div class="container">
                <h2 id="propiedades-cta-title">¿Buscas asesoría personalizada?</h2>
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
