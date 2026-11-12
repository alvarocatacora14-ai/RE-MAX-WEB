<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Configuración global (Solo Administrador)</p>
            <h1><span aria-hidden="true">☎</span> Información Global de Contacto</h1>
            <p>Actualiza los teléfonos, correos, horarios y números de WhatsApp visibles en el sitio público.</p>
        </div>
    </div>

    <div class="card-box" style="max-width: 760px;">
        <form class="content-form" style="margin-top: 0;" wire:submit="guardarConfiguracion">
            <label>
                Nombre comercial de la empresa
                <input type="text" wire:model="nombre_empresa" maxlength="120" required>
                @error('nombre_empresa') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Teléfono visible en pie de página
                <input type="text" wire:model="telefono_visible" maxlength="30" placeholder="+51 999 999 999" required>
                @error('telefono_visible') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                WhatsApp general (solo dígitos con código de país, ej. 51999999999)
                <input type="text" wire:model="whatsapp_general" maxlength="20" required>
                @error('whatsapp_general') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                WhatsApp para botón «Postular por WhatsApp» (solo dígitos con código de país)
                <input type="text" wire:model="whatsapp_postulacion" maxlength="20" required>
                @error('whatsapp_postulacion') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Correo electrónico de contacto
                <input type="email" wire:model="correo_contacto" maxlength="150" required>
                @error('correo_contacto') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Dirección o referencia (opcional)
                <input type="text" wire:model="direccion" maxlength="255">
                @error('direccion') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Horario de lunes a viernes
                <input type="text" wire:model="horario_lunes_viernes" maxlength="120" required>
                @error('horario_lunes_viernes') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Horario de sábados
                <input type="text" wire:model="horario_sabados" maxlength="120" required>
                @error('horario_sabados') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Guardar configuración de contacto</span>
                <span wire:loading>Guardando...</span>
            </button>
        </form>
    </div>
</section>
