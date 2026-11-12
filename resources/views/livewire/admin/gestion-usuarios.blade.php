<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Control de accesos (Solo Administrador)</p>
            <h1><span aria-hidden="true">⚙</span> Gestión de Usuarios y Roles</h1>
            <p>Administra las cuentas con rol ADMINISTRADOR y MARKETING.</p>
        </div>
        <button type="button" wire:click="nuevoUsuario">＋ Nuevo usuario</button>
    </div>

    <div class="management-layout">
        <div class="data-area">
            <div class="list-toolbar">
                <strong>Usuarios registrados</strong>
                <span class="count-label">{{ $usuarios->count() }} usuarios</span>
            </div>

            <div class="list-head list-head--users">
                <span>Nombre</span>
                <span>Correo electrónico</span>
                <span>Rol</span>
                <span>Estado</span>
                <span>Acciones</span>
            </div>

            <div class="content-list">
                @foreach($usuarios as $usuario)
                    <article class="item user-item" wire:key="usuario-{{ $usuario->id }}">
                        <div>
                            <h3>{{ $usuario->nombre }}</h3>
                        </div>
                        <div>
                            <p>{{ $usuario->correo }}</p>
                        </div>
                        <div>
                            <span class="role-badge">{{ $usuario->rol }}</span>
                        </div>
                        <div>
                            <span class="status {{ $usuario->activo ? '' : 'off' }}">
                                {{ $usuario->activo ? 'Activo' : 'Suspendido' }}
                            </span>
                        </div>
                        <div class="item-actions">
                            <button type="button" title="Editar usuario" wire:click="editarUsuario({{ $usuario->id }})">✎</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>

        <form class="content-form editor-panel" wire:submit="guardarUsuario">
            <div class="form-header">
                <h2>{{ $usuarioId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
                @if($usuarioId)
                    <button class="icon-button" type="button" wire:click="limpiarFormulario" aria-label="Limpiar">×</button>
                @endif
            </div>

            <label>
                Nombre completo
                <input type="text" wire:model="nombre" maxlength="120" required>
                @error('nombre') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Correo electrónico
                <input type="email" wire:model="correo" maxlength="150" required>
                @error('correo') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Rol del usuario
                <select wire:model="rol" required>
                    <option value="MARKETING">MARKETING</option>
                    <option value="ADMINISTRADOR">ADMINISTRADOR</option>
                </select>
                @error('rol') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label>
                Contraseña {{ $usuarioId ? '(dejar vacío para no cambiar)' : '(mínimo 8 caracteres)' }}
                <input type="password" wire:model="password" autocomplete="new-password" {{ $usuarioId ? '' : 'required' }}>
                @error('password') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label style="display: flex; align-items: center; gap: .6rem; cursor: pointer;">
                <input type="checkbox" wire:model="activo" style="width: auto;">
                <span>Cuenta activa</span>
            </label>

            <button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>{{ $usuarioId ? 'Actualizar usuario' : 'Crear usuario' }}</span>
                <span wire:loading>Guardando...</span>
            </button>
        </form>
    </div>
</section>
