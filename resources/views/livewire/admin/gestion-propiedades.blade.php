<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Administración de contenido</p>
            <h1><span aria-hidden="true">▦</span> Gestión de Propiedades Destacadas</h1>
            <p>Registra, edita o desactiva las propiedades que se muestran en el sitio público.</p>
        </div>
        <button type="button" wire:click="nuevaPropiedad">＋ Agregar propiedad</button>
    </div>

    <div class="management-layout">
        <div class="data-area">
            <div class="list-toolbar">
                <input
                    type="search"
                    class="search-input"
                    wire:model.live.debounce.250ms="busqueda"
                    placeholder="Buscar por nombre o ubicación..."
                >
                <span class="count-label">
                    {{ $propiedades->count() }} {{ $propiedades->count() === 1 ? 'propiedad' : 'propiedades' }}
                </span>
            </div>

            <div class="list-head list-head--properties">
                <span>Imagen</span>
                <span>Nombre del proyecto</span>
                <span>Ubicación / Oficina</span>
                <span>Precio</span>
                <span>Estado</span>
                <span>Acciones</span>
            </div>

            <div class="content-list">
                @forelse($propiedades as $propiedad)
                    <article class="item property-item {{ $propiedad->destacada ? 'is-featured-row' : '' }}" wire:key="propiedad-{{ $propiedad->id }}">
                        <img src="{{ $propiedad->url_imagen }}" alt="{{ $propiedad->nombre }}">
                        <div>
                            <h3>
                                {{ $propiedad->nombre }}
                                @if($propiedad->destacada)
                                    <span class="featured-badge">★ Destacada</span>
                                @endif
                            </h3>
                            <p><a href="{{ $propiedad->enlace_remax }}" target="_blank" rel="noopener noreferrer">Ver ficha RE/MAX ↗</a></p>
                        </div>
                        <div>
                            <p>⌖ {{ $propiedad->ubicacion }}</p>
                            <div style="margin-top: .3rem;">
                                @if($propiedad->nombre_oficina)
                                    <span class="office-badge">Oficina: {{ $propiedad->nombre_oficina }}</span>
                                @else
                                    <span class="status warn">Sin oficina asignada</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            @if($propiedad->precio_formateado)
                                <strong>{{ $propiedad->precio_formateado }}</strong>
                            @else
                                <span class="status warn">Sin precio</span>
                            @endif
                        </div>
                        <div>
                            <span class="status {{ $propiedad->activo ? '' : 'off' }}">
                                {{ $propiedad->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="item-actions">
                            <button
                                type="button"
                                class="star-toggle {{ $propiedad->destacada ? 'is-featured' : '' }}"
                                title="{{ $propiedad->destacada ? 'Quitar de destacadas' : 'Marcar como destacada' }}"
                                aria-label="{{ $propiedad->destacada ? 'Quitar de destacadas' : 'Marcar como destacada' }}"
                                aria-pressed="{{ $propiedad->destacada ? 'true' : 'false' }}"
                                wire:click="alternarDestacada({{ $propiedad->id }})"
                            >{{ $propiedad->destacada ? '★' : '☆' }}</button>
                            <button type="button" title="Editar" wire:click="editarPropiedad({{ $propiedad->id }})">✎</button>
                            <button
                                type="button"
                                class="delete"
                                title="Eliminar"
                                wire:click="eliminarPropiedad({{ $propiedad->id }})"
                                wire:confirm="¿Deseas eliminar «{{ $propiedad->nombre }}»?"
                            >⌫</button>
                        </div>
                    </article>
                @empty
                    <div style="padding: 1.5rem; text-align: center; color: var(--muted);">
                        No se encontraron propiedades registradas.
                    </div>
                @endforelse
            </div>
        </div>

        @if($mostrarFormulario)
            <form class="content-form editor-panel" wire:submit="guardarPropiedad">
                <div class="form-header">
                    <h2>{{ $propiedadId ? 'Editar propiedad' : 'Nueva propiedad' }}</h2>
                    <button class="icon-button" type="button" wire:click="limpiarFormulario" aria-label="Cerrar editor">×</button>
                </div>

                <label>
                    Nombre del proyecto o inmueble
                    <input type="text" wire:model="nombre" maxlength="140" required>
                    @error('nombre') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Ubicación
                    <input type="text" wire:model="ubicacion" maxlength="140" required>
                    @error('ubicacion') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label>
                    Oficina responsable
                    <select wire:model="oficina" required>
                        <option value="">Selecciona una oficina...</option>
                        @foreach(\App\Models\Propiedad::OFICINAS as $codigoOficina => $nombreOficina)
                            <option value="{{ $codigoOficina }}">{{ $nombreOficina }}</option>
                        @endforeach
                    </select>
                    @error('oficina') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div class="price-grid">
                    <label>
                        Moneda
                        <select wire:model="moneda">
                            <option value="USD">USD ($)</option>
                            <option value="PEN">PEN (S/)</option>
                        </select>
                        @error('moneda') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label>
                        Precio (opcional)
                        <input type="number" step="0.01" min="0" wire:model="precio" placeholder="Ej. 125000.00">
                        @error('precio') <span class="field-error">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label>
                    Enlace a ficha de RE/MAX Perú
                    <input type="url" wire:model="enlace_remax" maxlength="500" placeholder="https://www.remax.pe/..." required>
                    @error('enlace_remax') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div class="office-image-preview-box">
                    <span class="readonly-meta-label">Vista previa de fotografía</span>
                    @if($imagenArchivo && method_exists($imagenArchivo, 'temporaryUrl'))
                        <img class="office-preview-img" src="{{ $imagenArchivo->temporaryUrl() }}" alt="Vista previa de nueva fotografía para {{ $nombre ?: 'el proyecto' }}">
                        <div class="preview-caption-row">
                            <small class="preview-caption">Nueva fotografía seleccionada (sin guardar aún)</small>
                            <button type="button" class="preview-clear-btn" wire:click="limpiarImagenSeleccionada">Quitar selección</button>
                        </div>
                    @elseif($urlImagenActual)
                        <img class="office-preview-img" src="{{ $urlImagenActual }}" alt="Fotografía actual de {{ $nombre }}">
                        <small class="preview-caption">Fotografía actual del proyecto o propiedad</small>
                    @else
                        <div class="office-preview-placeholder">
                            <strong>{{ $nombre ?: 'Nueva propiedad' }}</strong>
                            <span>Sin fotografía seleccionada</span>
                        </div>
                    @endif
                </div>

                <label>
                    Fotografía de la propiedad {{ $propiedadId ? '(dejar vacío para conservar actual)' : '' }}
                    <input type="file" wire:model="imagenArchivo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    @error('imagenArchivo') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label style="display: flex; align-items: center; gap: .6rem; cursor: pointer;">
                    <input type="checkbox" wire:model="activo" style="width: auto;">
                    <span>Visible en el sitio público</span>
                </label>

                <button type="submit" wire:loading.attr="disabled">
                    <span wire:loading.remove>Guardar cambios</span>
                    <span wire:loading>Guardando...</span>
                </button>
            </form>
        @else
            <div class="editor-panel" style="color: var(--muted); text-align: center;">
                <p>Selecciona una propiedad para editarla o haz clic en <strong>«＋ Agregar propiedad»</strong>.</p>
            </div>
        @endif
    </div>
</section>
