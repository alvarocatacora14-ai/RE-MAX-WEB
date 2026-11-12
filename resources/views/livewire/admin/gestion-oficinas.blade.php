<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Configuración de sedes (Solo Administrador)</p>
            <h1><span aria-hidden="true">🏢</span> Gestión de Oficinas</h1>
            <p>Actualiza la dirección, teléfonos, correo, horario, ubicación y fotografía individual de cada una de las cuatro oficinas.</p>
        </div>
    </div>

    <div class="management-layout">
        <div class="data-area">
            <div class="list-toolbar">
                <strong>Sedes oficiales de RE/MAX Zona</strong>
                <span class="count-label">4 oficinas establecidas</span>
            </div>

            <div class="list-head list-head--offices">
                <span>Imagen</span>
                <span>Sede / Código</span>
                <span>Dirección y horario</span>
                <span>Canales de contacto</span>
                <span>Acción</span>
            </div>

            <div class="content-list">
                @foreach($oficinas as $sede)
                    <article
                        class="item office-item {{ $oficinaId === $sede->id ? 'is-selected-row' : '' }}"
                        wire:key="oficina-admin-{{ $sede->id }}"
                    >
                        @if($sede->url_imagen)
                            <img src="{{ $sede->url_imagen }}" alt="Oficina {{ $sede->nombre }}">
                        @else
                            <div class="office-thumb-placeholder" aria-label="Sin fotografía cargada">
                                <span>{{ substr($sede->nombre, 0, 2) }}</span>
                            </div>
                        @endif

                        <div>
                            <h3>
                                {{ $sede->nombre }}
                                <span class="office-badge">{{ $sede->codigo }}</span>
                            </h3>
                            <p>Ruta pública: <a href="{{ route('oficinas.propiedades', ['oficina' => $sede->slug]) }}" target="_blank" rel="noopener noreferrer">/oficinas/{{ $sede->slug }} ↗</a></p>
                        </div>

                        <div>
                            @if($sede->direccion)
                                <p>⌖ {{ $sede->direccion }}</p>
                            @else
                                <p><span class="status warn">Dirección no configurada</span></p>
                            @endif
                            @if($sede->horario)
                                <p style="margin-top: .25rem;">🕒 {{ $sede->horario }}</p>
                            @endif
                        </div>

                        <div>
                            @if($sede->telefono || $sede->whatsapp || $sede->correo)
                                @if($sede->telefono)
                                    <p>☎ {{ $sede->telefono }}</p>
                                @endif
                                @if($sede->whatsapp)
                                    <p>💬 {{ $sede->whatsapp }}</p>
                                @endif
                                @if($sede->correo)
                                    <p>✉ {{ $sede->correo }}</p>
                                @endif
                            @else
                                <span class="status off">Sin contacto individual</span>
                            @endif
                        </div>

                        <div class="item-actions">
                            <button
                                type="button"
                                title="Editar oficina {{ $sede->nombre }}"
                                aria-label="Editar oficina {{ $sede->nombre }}"
                                wire:click="seleccionarOficina({{ $sede->id }})"
                            >✎</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        @if($oficinaId)
            <form class="content-form editor-panel" wire:submit="guardarOficina">
                <div class="form-header">
                    <h2>Editar sede: {{ $nombre ?: $codigo }}</h2>
                    <span class="office-badge">{{ $codigo }}</span>
                </div>

                <div class="readonly-meta-grid">
                    <div>
                        <span class="readonly-meta-label">Código oficial</span>
                        <strong>{{ $codigo }}</strong>
                    </div>
                    <div>
                        <span class="readonly-meta-label">Ruta de catálogo</span>
                        <strong>/oficinas/{{ $slug }}</strong>
                    </div>
                </div>

                <label>
                    Nombre de la sede
                    <input type="text" wire:model="nombre" maxlength="80" required>
                    @error('nombre') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Dirección física (opcional)
                    <input type="text" wire:model="direccion" maxlength="255" placeholder="Pendiente de configuración">
                    @error('direccion') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div class="price-grid" style="grid-template-columns: 1fr 1fr;">
                    <label>
                        Teléfono (opcional)
                        <input type="text" wire:model="telefono" maxlength="30" placeholder="Ej. +51 999 999 999">
                        @error('telefono') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        WhatsApp (opcional, solo dígitos)
                        <input type="text" wire:model="whatsapp" maxlength="20" placeholder="Ej. 51999999999">
                        @error('whatsapp') <span class="field-error">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label>
                    Correo electrónico de la oficina (opcional)
                    <input type="email" wire:model="correo" maxlength="150" placeholder="Pendiente de configuración">
                    @error('correo') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Horario de atención (opcional)
                    <input type="text" wire:model="horario" maxlength="180" placeholder="Pendiente de configuración">
                    @error('horario') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Enlace de Google Maps (opcional)
                    <input type="url" wire:model="enlace_google_maps" maxlength="500" placeholder="https://maps.google.com/...">
                    @error('enlace_google_maps') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div class="office-image-preview-box">
                    <span class="readonly-meta-label">Vista previa de fotografía</span>
                    @if($imagenArchivo && method_exists($imagenArchivo, 'temporaryUrl'))
                        <img class="office-preview-img" src="{{ $imagenArchivo->temporaryUrl() }}" alt="Vista previa de nueva fotografía para {{ $nombre }}">
                        <small class="preview-caption">Nueva fotografía seleccionada (sin guardar aún)</small>
                    @elseif($urlImagenActual)
                        <img class="office-preview-img" src="{{ $urlImagenActual }}" alt="Fotografía actual de {{ $nombre }}">
                        <small class="preview-caption">Fotografía actual de la sede</small>
                    @else
                        <div class="office-preview-placeholder">
                            <strong>Sede {{ $nombre }}</strong>
                            <span>Sin fotografía cargada (se muestra espacio institucional neutro)</span>
                        </div>
                    @endif
                </div>

                <label>
                    Fotografía representativa (dejar vacío para conservar actual)
                    <input type="file" wire:model="imagenArchivo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    @error('imagenArchivo') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Guardar cambios de la oficina</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </form>
        @endif
    </div>
</section>
