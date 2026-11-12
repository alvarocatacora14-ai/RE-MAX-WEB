<div>
    <main id="contenido">
        <section class="page-banner" aria-labelledby="contacto-hero-title">
            <div class="container">
                <p class="eyebrow">{{ $contacto->nombre_empresa }}</p>
                <h1 id="contacto-hero-title">Contáctanos</h1>
                <p>Estamos listos para asesorarte en la compra, venta, alquiler o inversión de tu propiedad.</p>
            </div>
        </section>

        <section class="section" aria-labelledby="canales-contacto-title">
            <div class="container">
                <div class="section-heading">
                    <p class="eyebrow eyebrow--blue">Atención directa</p>
                    <h2 id="canales-contacto-title">Medios de contacto oficiales</h2>
                </div>

                <div class="contact-layout">
                    <div class="contact-channels">
                        <article class="card info-card">
                            <h3>Teléfono y WhatsApp</h3>
                            @if($contacto->telefono_visible)
                                <p>
                                    <strong>Teléfono:</strong>
                                    <a href="tel:{{ $contacto->telefono_enlace }}">{{ $contacto->telefono_visible }}</a>
                                </p>
                            @else
                                <p><strong>Teléfono:</strong> Pendiente de configuración</p>
                            @endif

                            @if($contacto->whatsapp_general)
                                <p>
                                    <strong>WhatsApp:</strong>
                                    <a href="https://wa.me/{{ $contacto->whatsapp_general }}" target="_blank" rel="noopener noreferrer">
                                        Enviar mensaje por WhatsApp
                                    </a>
                                </p>
                            @else
                                <p><strong>WhatsApp:</strong> Pendiente de configuración</p>
                            @endif
                        </article>

                        <article class="card info-card">
                            <h3>Correo electrónico</h3>
                            @if($contacto->correo_contacto)
                                <p>
                                    <a href="mailto:{{ $contacto->correo_contacto }}">{{ $contacto->correo_contacto }}</a>
                                </p>
                            @else
                                <p>Pendiente de configuración</p>
                            @endif
                        </article>

                        <article class="card info-card">
                            <h3>Dirección y cobertura</h3>
                            <p>{{ $contacto->direccion ?: 'Pendiente de configuración' }}</p>
                        </article>

                        <article class="card info-card">
                            <h3>Horario de atención</h3>
                            <p>{{ $contacto->horario_lunes_viernes ?: 'Pendiente de configuración' }}</p>
                            <p>{{ $contacto->horario_sabados ?: 'Pendiente de configuración' }}</p>
                        </article>
                    </div>

                    <div class="card contact-form-card">
                        <h3>Prepara tu consulta</h3>
                        <p class="contact-form-intro">
                            Completa tus datos para dirigir tu consulta directamente a través de nuestros canales oficiales de atención.
                        </p>

                        <form wire:submit="prepararConsulta" class="contact-form" novalidate>
                            <div class="form-field">
                                <label for="contacto-nombre">Nombre completo *</label>
                                <input
                                    id="contacto-nombre"
                                    type="text"
                                    wire:model="nombre"
                                    placeholder="Ingresa tu nombre y apellidos"
                                    required
                                >
                                @error('nombre')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-field">
                                    <label for="contacto-telefono">Teléfono o celular *</label>
                                    <input
                                        id="contacto-telefono"
                                        type="tel"
                                        wire:model="telefono"
                                        placeholder="Ej. +51 999 999 999"
                                        required
                                    >
                                    @error('telefono')
                                        <span class="field-error" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-field">
                                    <label for="contacto-correo">Correo electrónico (opcional)</label>
                                    <input
                                        id="contacto-correo"
                                        type="email"
                                        wire:model="correo"
                                        placeholder="tucorreo@ejemplo.com"
                                    >
                                    @error('correo')
                                        <span class="field-error" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-field">
                                <label for="contacto-asunto">Motivo de la consulta *</label>
                                <select id="contacto-asunto" wire:model="asunto" required>
                                    <option value="Consulta sobre propiedades">Consulta sobre propiedades</option>
                                    <option value="Venta o alquiler de inmueble">Venta o alquiler de inmueble</option>
                                    <option value="Inversión inmobiliaria">Inversión inmobiliaria</option>
                                    <option value="Postulación / Trabaja con nosotros">Postulación / Trabaja con nosotros</option>
                                    <option value="Otra consulta">Otra consulta</option>
                                </select>
                                @error('asunto')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-field">
                                <label for="contacto-mensaje">Mensaje *</label>
                                <textarea
                                    id="contacto-mensaje"
                                    rows="4"
                                    wire:model="mensaje"
                                    placeholder="Cuéntanos cómo podemos ayudarte..."
                                    required
                                ></textarea>
                                @error('mensaje')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="button button--primary">
                                    Preparar consulta
                                </button>
                                @if($consultaPreparada || $nombre !== '' || $telefono !== '' || $correo !== '' || $mensaje !== '')
                                    <button type="button" class="button button--outline" wire:click="limpiarFormulario">
                                        Limpiar
                                    </button>
                                @endif
                            </div>
                        </form>

                        @if($consultaPreparada)
                            <div class="prepared-inquiry-box" role="status">
                                <h4>Tu consulta está lista para enviarse</h4>
                                <p>Elige el canal oficial por el que deseas comunicarte con {{ $contacto->nombre_empresa }}:</p>
                                <div class="prepared-inquiry-actions">
                                    @if($contacto->whatsapp_general)
                                        <a
                                            class="button button--accent"
                                            href="{{ $enlaceWhatsappConsulta }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Enviar por WhatsApp
                                        </a>
                                    @endif

                                    @if($contacto->correo_contacto)
                                        <a
                                            class="button button--primary"
                                            href="{{ $enlaceCorreoConsulta }}"
                                        >
                                            Enviar por correo electrónico
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="cta" aria-labelledby="contacto-cta-title">
            <div class="container">
                <h2 id="contacto-cta-title">Trabaja con nosotros</h2>
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
