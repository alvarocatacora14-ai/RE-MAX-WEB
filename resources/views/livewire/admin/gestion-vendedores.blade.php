<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Padrón de asesores</p>
            <h1><span aria-hidden="true">♟</span> Gestión de Vendedores</h1>
            <p>Registra y actualiza a los vendedores de la oficina. Los cambios aquí no alteran los rankings históricos cerrados.</p>
        </div>
        <button type="button" wire:click="nuevoVendedor">＋ Nuevo vendedor</button>
    </div>

    <div class="management-layout">
        <div class="data-area">
            <div class="list-toolbar">
                <input
                    type="search"
                    class="search-input"
                    wire:model.live.debounce.250ms="busqueda"
                    placeholder="Buscar por nombres, apellidos o celular..."
                >
                <span class="count-label">
                    {{ $vendedores->count() }} {{ $vendedores->count() === 1 ? 'vendedor' : 'vendedores' }}
                </span>
            </div>

            <div class="list-head list-head--agents">
                <span>Foto</span>
                <span>Nombres y apellidos</span>
                <span>Celular</span>
                <span>Estado</span>
                <span>Acciones</span>
            </div>

            <div class="content-list">
                @forelse($vendedores as $vendedor)
                    <article class="item agent-item" wire:key="vendedor-{{ $vendedor->id }}">
                        <img src="{{ $vendedor->url_foto }}" alt="{{ $vendedor->nombre_completo }}">
                        <div>
                            <h3>{{ $vendedor->nombre_completo }}</h3>
                            <p>Nombres: {{ $vendedor->nombres }} | Apellidos: {{ $vendedor->apellidos }}</p>
                        </div>
                        <div>
                            <p>{{ $vendedor->celular }}</p>
                        </div>
                        <div>
                            <span class="status {{ $vendedor->activo ? '' : 'off' }}">
                                {{ $vendedor->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                        <div class="item-actions">
                            <button type="button" title="Editar vendedor" wire:click="editarVendedor({{ $vendedor->id }})">✎</button>
                        </div>
                    </article>
                @empty
                    <div style="padding: 1.5rem; text-align: center; color: var(--muted);">
                        No se encontraron vendedores registrados.
                    </div>
                @endforelse
            </div>
        </div>

        <form class="content-form editor-panel" wire:submit="guardarVendedor">
            <div class="form-header">
                <h2>{{ $vendedorId ? 'Editar vendedor' : 'Registrar vendedor' }}</h2>
                @if($vendedorId)
                    <button class="icon-button" type="button" wire:click="limpiarFormulario" aria-label="Limpiar formulario">×</button>
                @endif
            </div>

            <label>
                Nombres
                <input type="text" wire:model="nombres" maxlength="100" required>
                @error('nombres') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Apellidos
                <input type="text" wire:model="apellidos" maxlength="100" required>
                @error('apellidos') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Número de celular
                <input type="text" wire:model="celular" maxlength="20" placeholder="969 322 977" required>
                @error('celular') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <div class="office-image-preview-box">
                <span class="readonly-meta-label">Vista previa de fotografía</span>
                @if($fotoArchivo && method_exists($fotoArchivo, 'temporaryUrl'))
                    <img class="office-preview-img" src="{{ $fotoArchivo->temporaryUrl() }}" alt="Vista previa de nueva fotografía para {{ trim($nombres . ' ' . $apellidos) ?: 'el vendedor' }}">
                    <div class="preview-caption-row">
                        <small class="preview-caption">Nueva fotografía seleccionada (sin guardar aún)</small>
                        <button type="button" class="preview-clear-btn" wire:click="limpiarFotoSeleccionada">Quitar selección</button>
                    </div>
                @elseif($urlFotoActual)
                    <img class="office-preview-img" src="{{ $urlFotoActual }}" alt="Fotografía actual de {{ trim($nombres . ' ' . $apellidos) }}">
                    <small class="preview-caption">Fotografía actual del vendedor</small>
                @else
                    <div class="office-preview-placeholder">
                        <strong>{{ trim($nombres . ' ' . $apellidos) ?: 'Nuevo vendedor' }}</strong>
                        <span>Sin fotografía seleccionada</span>
                    </div>
                @endif
            </div>

            <label>
                Fotografía {{ $vendedorId ? '(dejar vacío para conservar actual)' : '' }}
                <input type="file" wire:model="fotoArchivo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                @error('fotoArchivo') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: flex; align-items: center; gap: .6rem; cursor: pointer;">
                <input type="checkbox" wire:model="activo" style="width: auto;">
                <span>Activo (disponible para nuevos rankings)</span>
            </label>

            <button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $vendedorId ? 'Actualizar vendedor' : 'Registrar vendedor' }}</span>
                <span wire:loading>Guardando...</span>
            </button>
        </form>
    </div>
</section>
