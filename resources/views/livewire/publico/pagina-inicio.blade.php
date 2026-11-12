<div>
    <main id="contenido">
        <section class="hero" id="inicio" aria-labelledby="hero-title">
            <img class="hero__image" src="{{ $portada->url_imagen }}" alt="Vista panorámica de una propiedad">
            <div class="hero__overlay"></div>
            <div class="hero__content container">
                <p class="eyebrow">{{ $contacto->nombre_empresa }}</p>
                <h1 id="hero-title">Encuentra tu propiedad ideal</h1>
                <p>Compra, vende o alquila con los mejores asesores inmobiliarios del sur del Perú.</p>
                <a class="button button--accent" href="#propiedades">Ver propiedades</a>
            </div>
        </section>

        <section class="section" aria-labelledby="tops-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Nuestro equipo</p>
                    <h2 id="tops-title">Nuestros destacados</h2>
                    @if($rankingPublicado)
                        <p class="section-subtitle">
                            Vendedores estrella — Desempeño de {{ $rankingPublicado->etiqueta_desempeno }}
                        </p>
                    @endif
                </div>

                @if($rankingPublicado && $rankingPublicado->posiciones->count() === 3)
                    <div class="card-grid card-grid--agents" id="featured-agents">
                        @foreach($rankingPublicado->posiciones as $posicion)
                            <article class="card agent-card">
                                <span class="agent-badge agent-badge--{{ $posicion->puesto }}">
                                    @if($posicion->puesto === 1)
                                        1.er Puesto
                                    @elseif($posicion->puesto === 2)
                                        2.º Puesto
                                    @else
                                        3.er Puesto
                                    @endif
                                </span>
                                <img
                                    src="{{ $posicion->url_foto_snapshot }}"
                                    alt="{{ $posicion->nombre_completo_snapshot }}, asesor inmobiliario"
                                >
                                <h3>{{ $posicion->nombre_completo_snapshot }}</h3>
                                <a
                                    href="https://api.whatsapp.com/send/?phone={{ $posicion->celular_whatsapp_snapshot }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    WhatsApp: {{ $posicion->celular_snapshot }}
                                </a>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="ranking-updating-box" role="status">
                        Ranking mensual en actualización. Pronto conocerás a nuestros vendedores destacados del mes
                    </div>
                @endif
            </div>
        </section>

        <section class="section section--muted" id="propiedades" aria-labelledby="properties-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Encuentra tu próximo hogar</p>
                    <h2 id="properties-title">Propiedades destacadas</h2>
                </div>

                <div class="card-grid card-grid--properties" id="featured-properties">
                    @forelse($propiedadesDestacadas as $propiedad)
                        <article class="card property-card">
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
                        <p>Pronto publicaremos nuevas propiedades destacadas.</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="cta" aria-labelledby="cta-title">
            <div class="container">
                <h2 id="cta-title">Trabaja con nosotros</h2>
                <p>Desarrolla tu carrera con la red RE/MAX y alcanza tu máximo potencial profesional.</p>
                <a
                    class="button button--accent"
                    href="https://api.whatsapp.com/send/?phone={{ $contacto->whatsapp_postulacion }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Postular por WhatsApp
                </a>
            </div>
        </section>
    </main>
</div>
