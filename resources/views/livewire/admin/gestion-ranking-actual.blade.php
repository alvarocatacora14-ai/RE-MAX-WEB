<section>
    @if($mensajeExito)
        <p class="message success" role="status">{{ $mensajeExito }}</p>
    @endif
    @if($mensajeError)
        <p class="message" role="alert">{{ $mensajeError }}</p>
    @endif

    <div class="page-heading">
        <div>
            <p class="eyebrow">Vendedores Estrella del Mes</p>
            <h1><span aria-hidden="true">★</span> Gestión del Ranking Mensual</h1>
            <p>
                Período de desempeño evaluado: <strong>{{ $etiquetaDesempeno }}</strong> |
                Mes de publicación pública: <strong>{{ $etiquetaPublicacion }}</strong>
            </p>
        </div>
    </div>

    @if($dentroDePlazo)
        <div class="ranking-banner">
            <strong>Plazo habilitado (Días 1 al 3 de {{ $etiquetaPublicacion }}):</strong>
            Puedes preparar, publicar o modificar el ranking correspondiente al desempeño de <strong>{{ $etiquetaDesempeno }}</strong>
            hasta el día 3 de {{ $etiquetaPublicacion }} a las 11:59 p. m. (Hora Perú: {{ $ahoraLima->format('d/m/Y H:i') }}).
            @if($rankingActual)
                <br>Estado actual del registro: <strong>{{ $rankingActual->estado }}</strong>
            @endif
        </div>

        <div class="card-box">
            <form wire:submit="publicarRanking" class="content-form" style="margin-top: 0;">
                <div class="podium-grid">
                    <div class="podium-card">
                        <span class="status">1.er Puesto (Primer Puesto)</span>
                        <label style="margin-top: 1rem; text-align: left;">
                            Seleccionar vendedor
                            <select wire:model="puesto1" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($vendedoresActivos as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }} ({{ $vendedor->celular }})</option>
                                @endforeach
                            </select>
                            @error('puesto1') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="podium-card">
                        <span class="status">2.º Puesto (Segundo Puesto)</span>
                        <label style="margin-top: 1rem; text-align: left;">
                            Seleccionar vendedor
                            <select wire:model="puesto2" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($vendedoresActivos as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }} ({{ $vendedor->celular }})</option>
                                @endforeach
                            </select>
                            @error('puesto2') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <div class="podium-card">
                        <span class="status">3.er Puesto (Tercer Puesto)</span>
                        <label style="margin-top: 1rem; text-align: left;">
                            Seleccionar vendedor
                            <select wire:model="puesto3" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($vendedoresActivos as $vendedor)
                                    <option value="{{ $vendedor->id }}">{{ $vendedor->nombre_completo }} ({{ $vendedor->celular }})</option>
                                @endforeach
                            </select>
                            @error('puesto3') <span class="field-error">{{ $message }}</span> @enderror
                        </label>
                    </div>
                </div>

                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: .75rem;">
                    @if(! $rankingActual || $rankingActual->estado === 'BORRADOR')
                        <button type="button" class="btn-secondary" wire:click="guardarBorrador" wire:loading.attr="disabled">
                            Guardar como Borrador
                        </button>
                    @endif

                    <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                        {{ $rankingActual && $rankingActual->estado === 'PUBLICADO' ? 'Actualizar Ranking Publicado' : 'Publicar Ranking en el Sitio Público' }}
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="ranking-banner locked">
            <strong>Plazo cerrado (A partir del día 4 de {{ $etiquetaPublicacion }}):</strong>
            El plazo para preparar, publicar o modificar el ranking del desempeño de <strong>{{ $etiquetaDesempeno }}</strong>
            finalizó el día 3 de {{ $etiquetaPublicacion }} a las 11:59 p. m. El período actual se encuentra bloqueado automáticamente.
        </div>
    @endif

    @if($rankingActual && $rankingActual->posiciones->count() === 3)
        <div class="card-box" style="margin-top: 1.5rem;">
            <h2>
                Vista del Ranking — Desempeño {{ $etiquetaDesempeno }}
                <span class="status {{ $rankingActual->estado === 'VENCIDO' ? 'off' : '' }}" style="margin-left: .5rem;">
                    {{ $rankingActual->estado }}
                </span>
            </h2>
            <p style="color: var(--muted);">
                Fotografías históricas y datos conservados de manera independiente para este período.
            </p>

            <div class="podium-grid">
                @foreach($rankingActual->posiciones as $pos)
                    <div class="podium-card">
                        <strong>Puesto #{{ $pos->puesto }}</strong>
                        <img src="{{ $pos->url_foto_snapshot }}" alt="{{ $pos->nombre_completo_snapshot }}">
                        <h3>{{ $pos->nombre_completo_snapshot }}</h3>
                        <p>Celular: {{ $pos->celular_snapshot }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @elseif(! $dentroDePlazo)
        <div class="card-box" style="margin-top: 1.5rem; text-align: center; color: var(--muted);">
            <p>
                El ranking correspondiente al desempeño de <strong>{{ $etiquetaDesempeno }}</strong> no fue publicado durante los días 1 al 3 de {{ $etiquetaPublicacion }}.
                Por regla de negocio, no se permite su publicación fuera de plazo. El próximo período se habilitará el día 1 del siguiente mes.
            </p>
        </div>
    @endif
</section>
