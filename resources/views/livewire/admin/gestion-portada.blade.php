<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Configuración visual (Solo Administrador)</p>
            <h1><span aria-hidden="true">🖼</span> Portada Principal</h1>
            <p>Actualiza la fotografía principal (Hero) que se muestra al ingresar a la página de Inicio del sitio público.</p>
        </div>
    </div>

    <div class="card-box" style="max-width: 760px;">
        <form class="content-form" style="margin-top: 0;" wire:submit="guardarPortada">
            <div class="readonly-meta-grid">
                <div>
                    <span class="readonly-meta-label">Sección pública</span>
                    <strong>Portada de Inicio (/)</strong>
                </div>
                <div>
                    <span class="readonly-meta-label">Archivo activo en portada</span>
                    <strong>{{ $imagenActual }}</strong>
                </div>
            </div>

            <div class="office-image-preview-box">
                <span class="readonly-meta-label">Vista previa de fotografía de portada</span>
                @if($imagenArchivo && method_exists($imagenArchivo, 'isPreviewable') && $imagenArchivo->isPreviewable())
                    <img
                        class="office-preview-img"
                        style="height: 260px;"
                        src="{{ $imagenArchivo->temporaryUrl() }}"
                        alt="Vista previa de nueva fotografía para la portada principal"
                    >
                    <div class="preview-caption-row">
                        <small class="preview-caption">Nueva fotografía seleccionada (sin guardar aún)</small>
                        <button type="button" class="preview-clear-btn" wire:click="limpiarImagenSeleccionada">
                            Quitar selección
                        </button>
                    </div>
                @elseif($urlImagenActual)
                    <img
                        class="office-preview-img"
                        style="height: 260px;"
                        src="{{ $urlImagenActual }}"
                        alt="Fotografía actual de la portada principal"
                    >
                    <small class="preview-caption">Fotografía actualmente publicada en la portada de Inicio</small>
                @else
                    <div class="office-preview-placeholder" style="height: 220px;">
                        <strong>Portada Principal</strong>
                        <span>Sin fotografía cargada</span>
                    </div>
                @endif
            </div>

            <label>
                Seleccionar nueva fotografía de portada (JPG, JPEG, PNG o WEBP — máx. 4 MB)
                <input
                    type="file"
                    wire:model="imagenArchivo"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                >
                @error('imagenArchivo') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar fotografía de portada</span>
                <span wire:loading>Guardando...</span>
            </button>
        </form>
    </div>
</section>
