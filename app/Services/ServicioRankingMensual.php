<?php

namespace App\Services;

use App\Models\PosicionRanking;
use App\Models\RankingMensual;
use App\Models\Vendedor;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ServicioRankingMensual
{
    /**
     * Sincroniza los estados vencidos (solo se invoca en el panel administrativo o comando consola,
     * nunca durante consultas del sitio público).
     */
    public function sincronizarEstadosVencidos(): void
    {
        $ahora = now('America/Lima');

        $rankingsAbiertos = RankingMensual::whereIn('estado', [
            RankingMensual::ESTADO_BORRADOR,
            RankingMensual::ESTADO_PUBLICADO,
        ])->get();

        foreach ($rankingsAbiertos as $ranking) {
            if (! RankingMensual::dentroDePlazoDeGestion((int) $ranking->anio_publicacion, (int) $ranking->mes_publicacion)) {
                DB::transaction(function () use ($ranking, $ahora) {
                    $bloqueado = RankingMensual::where('id', $ranking->id)->lockForUpdate()->first();
                    if (! $bloqueado || in_array($bloqueado->estado, [RankingMensual::ESTADO_CERRADO, RankingMensual::ESTADO_VENCIDO], true)) {
                        return;
                    }

                    $nuevoEstado = $bloqueado->estado === RankingMensual::ESTADO_PUBLICADO
                        ? RankingMensual::ESTADO_CERRADO
                        : RankingMensual::ESTADO_VENCIDO;

                    $bloqueado->update([
                        'estado' => $nuevoEstado,
                        'fecha_cierre' => $ahora,
                    ]);
                });
            }
        }
    }

    /**
     * Guarda el avance en BORRADOR o PUBLICA definitivamente el ranking vigente
     * aplicando lockForUpdate(), validación de plazo en America/Lima y limpieza compensatoria de fotografías.
     */
    public function guardarOPublicarRanking(array $vendedoresPorPuesto, int $usuarioId, bool $publicarDefinitivamente = true): RankingMensual
    {
        $archivosNuevos = [];
        $archivosObsoletos = [];

        try {
            $rankingResultado = DB::transaction(function () use (
                $vendedoresPorPuesto,
                $usuarioId,
                $publicarDefinitivamente,
                &$archivosNuevos,
                &$archivosObsoletos
            ) {
                // 1. Validación inicial de plazo en America/Lima
                $periodo = RankingMensual::periodoActual();
                if (! RankingMensual::dentroDePlazoDeGestion($periodo['anio_publicacion'], $periodo['mes_publicacion'])) {
                    throw new DomainException('El plazo de gestión del ranking (días 1 al 3 del mes) ha finalizado. El ranking se encuentra bloqueado.');
                }

                // 2. Garantizar cabecera del período actual y bloquear con lockForUpdate()
                RankingMensual::firstOrCreate(
                    [
                        'anio_desempeno' => $periodo['anio_desempeno'],
                        'mes_desempeno' => $periodo['mes_desempeno'],
                    ],
                    [
                        'anio_publicacion' => $periodo['anio_publicacion'],
                        'mes_publicacion' => $periodo['mes_publicacion'],
                        'estado' => RankingMensual::ESTADO_BORRADOR,
                        'creado_por' => $usuarioId,
                    ]
                );

                $ranking = RankingMensual::where('anio_desempeno', $periodo['anio_desempeno'])
                    ->where('mes_desempeno', $periodo['mes_desempeno'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // 3. Re-comprobar estado y plazo tras adquirir el bloqueo exclusivo
                if (
                    in_array($ranking->estado, [RankingMensual::ESTADO_CERRADO, RankingMensual::ESTADO_VENCIDO], true)
                    || ! RankingMensual::dentroDePlazoDeGestion((int) $ranking->anio_publicacion, (int) $ranking->mes_publicacion)
                ) {
                    throw new DomainException('El ranking ya se encuentra cerrado o fuera del plazo permitido de los primeros 3 días.');
                }

                // Si el ranking ya fue PUBLICADO, no puede volver a BORRADOR; cualquier edición dentro de los días 1..3 mantiene PUBLICADO
                $debeQuedarPublicado = $publicarDefinitivamente || $ranking->estado === RankingMensual::ESTADO_PUBLICADO;

                // 4. Validar las 3 posiciones (1, 2 y 3) y que sean 3 vendedores distintos
                $puesto1 = (int) ($vendedoresPorPuesto[1] ?? 0);
                $puesto2 = (int) ($vendedoresPorPuesto[2] ?? 0);
                $puesto3 = (int) ($vendedoresPorPuesto[3] ?? 0);
                $idsSeleccionados = [$puesto1, $puesto2, $puesto3];

                if (in_array(0, $idsSeleccionados, true)) {
                    throw new DomainException('Debes asignar los tres puestos obligatorios: Primer, Segundo y Tercer puesto.');
                }

                if (count(array_unique($idsSeleccionados)) !== 3) {
                    throw new DomainException('Un mismo vendedor no puede ocupar dos posiciones dentro del mismo ranking mensual.');
                }

                // 5. Bloquear los 3 vendedores con lockForUpdate() y comprobar que sigan activos y con datos y foto válida
                $vendedores = Vendedor::whereIn('id', $idsSeleccionados)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($vendedores->count() !== 3) {
                    throw new DomainException('Uno o más vendedores seleccionados no existen en el padrón.');
                }

                foreach ([1 => $puesto1, 2 => $puesto2, 3 => $puesto3] as $puesto => $vendedorId) {
                    /** @var Vendedor $vendedor */
                    $vendedor = $vendedores->get($vendedorId);

                    if (! $vendedor->activo) {
                        throw new DomainException("El vendedor «{$vendedor->nombre_completo}» está desactivado y no puede asignarse al ranking.");
                    }

                    if (blank($vendedor->nombres) || blank($vendedor->apellidos) || blank($vendedor->celular) || blank($vendedor->foto)) {
                        throw new DomainException("El vendedor asignado al puesto {$puesto} no tiene todos sus datos obligatorios completos.");
                    }

                    if (! Storage::disk('public')->exists($vendedor->foto)) {
                        throw new DomainException("No se encontró el archivo físico de la fotografía del vendedor en el puesto {$puesto}.");
                    }
                }

                // 6. Bloquear posiciones actuales y anotar sus fotos en $archivosObsoletos (SIN borrarlas antes del COMMIT)
                $posicionesPrevias = $ranking->posiciones()->lockForUpdate()->get();
                foreach ($posicionesPrevias as $posPrevia) {
                    if (! empty($posPrevia->foto_snapshot)) {
                        $archivosObsoletos[] = $posPrevia->foto_snapshot;
                    }
                }

                $ranking->posiciones()->delete();

                // 7. Crear copias físicas independientes de las 3 fotografías y registrar las 3 posiciones
                foreach ([1 => $puesto1, 2 => $puesto2, 3 => $puesto3] as $puesto => $vendedorId) {
                    /** @var Vendedor $vendedor */
                    $vendedor = $vendedores->get($vendedorId);

                    $rutaCopia = $this->generarCopiaFotografiaHistorica($ranking, $puesto, $vendedor);
                    $archivosNuevos[] = $rutaCopia;

                    $ranking->posiciones()->create([
                        'puesto' => $puesto,
                        'vendedor_id' => $vendedor->id,
                        'nombres_snapshot' => $vendedor->nombres,
                        'apellidos_snapshot' => $vendedor->apellidos,
                        'celular_snapshot' => $vendedor->celular,
                        'foto_snapshot' => $rutaCopia,
                    ]);
                }

                // 8. Verificación final de integridad dentro de la transacción
                $posicionesVerificadas = $ranking->posiciones()->get();
                if (
                    $posicionesVerificadas->count() !== 3 ||
                    $posicionesVerificadas->pluck('puesto')->sort()->values()->all() !== [1, 2, 3] ||
                    $posicionesVerificadas->pluck('vendedor_id')->unique()->count() !== 3 ||
                    $posicionesVerificadas->contains(fn (PosicionRanking $p) => blank($p->nombres_snapshot)
                        || blank($p->apellidos_snapshot)
                        || blank($p->celular_snapshot)
                        || blank($p->foto_snapshot)
                        || ! Storage::disk('public')->exists($p->foto_snapshot))
                ) {
                    throw new DomainException('No se pudo verificar la integridad de las 3 posiciones y sus fotografías.');
                }

                $ranking->update([
                    'estado' => $debeQuedarPublicado ? RankingMensual::ESTADO_PUBLICADO : RankingMensual::ESTADO_BORRADOR,
                    'fecha_publicacion' => $debeQuedarPublicado
                        ? ($ranking->fecha_publicacion ?? now('America/Lima'))
                        : null,
                    'actualizado_por' => $usuarioId,
                ]);

                return $ranking->fresh('posiciones');
            });

            // 9. POST-COMMIT: Limpiar las fotografías reemplazadas de este período que ya no estén referenciadas
            $this->limpiarFotografiasReemplazadasPostCommit($rankingResultado, $archivosObsoletos, $archivosNuevos);

            return $rankingResultado;

        } catch (Throwable $e) {
            // COMPENSACIÓN ANTE ROLLBACK: Eliminar únicamente las fotografías nuevas copiadas durante este intento fallido
            foreach ($archivosNuevos as $archivoNuevo) {
                if (Storage::disk('public')->exists($archivoNuevo)) {
                    Storage::disk('public')->delete($archivoNuevo);
                }
            }

            throw $e;
        }
    }

    private function generarCopiaFotografiaHistorica(RankingMensual $ranking, int $puesto, Vendedor $vendedor): string
    {
        $extension = strtolower(pathinfo($vendedor->foto, PATHINFO_EXTENSION)) ?: 'jpg';
        $mesFormateado = str_pad((string) $ranking->mes_desempeno, 2, '0', STR_PAD_LEFT);
        $directorioPeriodo = "rankings/{$ranking->anio_desempeno}/{$mesFormateado}";
        $nombreArchivo = "puesto-{$puesto}-vendedor-{$vendedor->id}-" . Str::uuid()->toString() . ".{$extension}";
        $rutaDestino = "{$directorioPeriodo}/{$nombreArchivo}";

        $copiado = Storage::disk('public')->copy($vendedor->foto, $rutaDestino);

        if (! $copiado || ! Storage::disk('public')->exists($rutaDestino)) {
            throw new DomainException("No se pudo crear la copia física de la fotografía para el puesto {$puesto}.");
        }

        return $rutaDestino;
    }

    private function limpiarFotografiasReemplazadasPostCommit(
        RankingMensual $ranking,
        array $archivosObsoletos,
        array $archivosNuevos
    ): void {
        // Nunca eliminar fotografías si el ranking está cerrado
        if ($ranking->estado === RankingMensual::ESTADO_CERRADO) {
            return;
        }

        $mesFormateado = str_pad((string) $ranking->mes_desempeno, 2, '0', STR_PAD_LEFT);
        $prefijoPermitido = "rankings/{$ranking->anio_desempeno}/{$mesFormateado}/";

        foreach (array_unique($archivosObsoletos) as $rutaAnterior) {
            if (in_array($rutaAnterior, $archivosNuevos, true)) {
                continue;
            }

            // Seguridad extra: solo permite limpiar archivos dentro del directorio del período actual en edición
            if (! str_starts_with($rutaAnterior, $prefijoPermitido)) {
                continue;
            }

            // Verificar que ninguna posición en la base de datos apunte a ese archivo
            $sigueReferenciado = PosicionRanking::where('foto_snapshot', $rutaAnterior)->exists();

            if (! $sigueReferenciado && Storage::disk('public')->exists($rutaAnterior)) {
                Storage::disk('public')->delete($rutaAnterior);
            }
        }
    }
}
