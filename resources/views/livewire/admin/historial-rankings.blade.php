<section>
    <div class="page-heading">
        <div>
            <p class="eyebrow">Consulta exclusiva del Administrador</p>
            <h1><span aria-hidden="true">⌚</span> Historial de Rankings Mensuales</h1>
            <p>Consulta en modo de solo lectura los rankings históricos por mes y año de desempeño.</p>
        </div>
    </div>

    <div class="card-box" style="margin-bottom: 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: end;">
            <label style="display: grid; gap: .35rem; font-weight: 700;">
                Mes de desempeño
                <select wire:model.live="mesFiltro" class="search-input" style="min-width: 190px;">
                    @foreach($meses as $numMes => $nombreMes)
                        <option value="{{ $numMes }}">{{ $nombreMes }}</option>
                    @endforeach
                </select>
            </label>

            <label style="display: grid; gap: .35rem; font-weight: 700;">
                Año de desempeño
                <select wire:model.live="anioFiltro" class="search-input" style="min-width: 140px;">
                    @foreach($aniosDisponibles as $anio)
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    @if($rankingConsultado && $rankingConsultado->posiciones->count() === 3)
        <div class="card-box">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2>Desempeño: {{ $rankingConsultado->etiqueta_desempeno }}</h2>
                    <p style="color: var(--muted);">
                        Publicado en: <strong>{{ $rankingConsultado->etiqueta_publicacion }}</strong>
                        @if($rankingConsultado->fecha_publicacion)
                            ({{ $rankingConsultado->fecha_publicacion->format('d/m/Y H:i') }})
                        @endif
                    </p>
                </div>
                <span class="status">{{ $rankingConsultado->estado }} — SOLO LECTURA</span>
            </div>

            <div class="podium-grid">
                @foreach($rankingConsultado->posiciones as $pos)
                    <div class="podium-card">
                        <span class="status">Puesto #{{ $pos->puesto }}</span>
                        <img src="{{ $pos->url_foto_snapshot }}" alt="{{ $pos->nombre_completo_snapshot }}">
                        <h3>{{ $pos->nombre_completo_snapshot }}</h3>
                        <p>Celular registrado: {{ $pos->celular_snapshot }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="card-box" style="text-align: center; color: var(--muted);">
            <p>No existe un ranking publicado o cerrado para el período de desempeño seleccionado ({{ $meses[$mesFiltro] ?? $mesFiltro }} {{ $anioFiltro }}).</p>
        </div>
    @endif

    @if($historialGeneral->isNotEmpty())
        <div class="card-box" style="margin-top: 1.5rem;">
            <h3>Períodos registrados en el historial</h3>
            <div style="display: flex; flex-wrap: wrap; gap: .6rem; margin-top: .75rem;">
                @foreach($historialGeneral as $item)
                    <button
                        type="button"
                        class="btn-secondary"
                        wire:click="$set('anioFiltro', {{ $item->anio_desempeno }}); $set('mesFiltro', {{ $item->mes_desempeno }})"
                    >
                        Desempeño {{ $item->etiqueta_desempeno }} (Pub. {{ $item->etiqueta_publicacion }} — {{ $item->estado }})
                    </button>
                @endforeach
            </div>
        </div>
    @endif
</section>
