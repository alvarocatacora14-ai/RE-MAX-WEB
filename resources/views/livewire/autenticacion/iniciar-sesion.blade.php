<main class="login-screen" aria-labelledby="login-title">
    <section class="login-card">
        <img class="login-logo" src="{{ asset('images/CONTENEDOR.png') }}" alt="RE/MAX Zona">
        <p class="eyebrow">Intranet inmobiliaria</p>
        <h1 id="login-title">Bienvenido</h1>
        <p class="login-copy">Ingresa con tu cuenta autorizada para administrar el contenido del sitio.</p>

        @if($errors->has('correo'))
            <p class="message" role="alert">{{ $errors->first('correo') }}</p>
        @endif

        <form wire:submit="autenticar">
            <label>
                Correo electrónico
                <input
                    type="email"
                    wire:model="correo"
                    autocomplete="email"
                    required
                >
                @error('correo')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <label>
                Contraseña
                <input
                    type="password"
                    wire:model="password"
                    autocomplete="current-password"
                    required
                >
                @error('password')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </label>

            <button type="submit" wire:loading.attr="disabled">
                <span wire:loading.remove>Iniciar sesión</span>
                <span wire:loading>Verificando...</span>
            </button>
        </form>
    </section>
</main>
