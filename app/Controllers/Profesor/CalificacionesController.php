<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\PonderacionCicloModel;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\EntregaTareaModel;
use App\Models\EntregaProyectoModel;
use App\Models\ExamenRespuestaModel;

class CalificacionesController extends BaseController
{
    protected $usuarioModel;
    protected $asignacionModel;
    protected $ponderacionModel;
    protected $mgaModel;
    protected $tareaEntregaModel;
    protected $proyectoEntregaModel;
    protected $examenRespModel;

    protected $db;


    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->usuarioModel = new UsuarioModel();
        $this->asignacionModel = new GrupoMateriaProfesorModel();
        $this->ponderacionModel = new PonderacionCicloModel();
        $this->mgaModel = new MateriaGrupoAlumnoModel();
        $this->tareaEntregaModel = new EntregaTareaModel();
        $this->proyectoEntregaModel = new EntregaProyectoModel();
        $this->examenRespModel = new ExamenRespuestaModel();
    }

    /* =====================================================
       📌 Vista principal
    ====================================================== */
    public function index()
    {
        $profesorId = session('id');

        $asignaciones = $this->asignacionModel
            ->obtenerAsignacionesPorProfesor($profesorId);

        return view('lms/profesor/extras/calificar', [
            'asignaciones' => $asignaciones
        ]);
    }

    public function obtenerAlumnos($asignacionId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Método inválido']);
        }

        $alumnos = $this->mgaModel->obtenerAlumnosPorAsignacion($asignacionId);

        return $this->response->setJSON([
            'alumnos' => $alumnos
        ]);
    }

    public function obtenerCriterios($cicloId, $parcial)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Método inválido']);
        }

        // Obtener ponderaciones
        $ponderaciones = $this->ponderacionModel->obtenerPorCicloYParcial($cicloId, $parcial);

        if (empty($ponderaciones)) {
            return $this->response->setJSON(['criterios' => []]);
        }

        // Obtener nombres de criterios
        $criterioModel = new \App\Models\CriterioEvaluacionModel();
        foreach ($ponderaciones as &$p) {
            $criterio = $criterioModel->find($p['criterio_id']);
            $p['criterio_nombre'] = $criterio['nombre'] ?? 'Criterio';
        }

        return $this->response->setJSON(['criterios' => $ponderaciones]);
    }

    public function obtenerValores($asignacionId, $parcial)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Método inválido']);
        }

        log_message('debug', "🔵 obtenerValores() => asignacionId={$asignacionId}, parcial={$parcial}");

        try {

            // 1) Ponderaciones cargadas para ese ciclo y parcial
            $asignacion = $this->asignacionModel->find($asignacionId);
            log_message('debug', '🧩 Asignacion encontrada: ' . json_encode($asignacion));

            if (!$asignacion) {
                log_message('error', "❌ No se encontró asignación {$asignacionId}");
                return $this->response->setJSON(['error' => 'Asignación no encontrada']);
            }

            $cicloId = $asignacion['ciclo_id'];
            log_message('debug', "📚 cicloId = {$cicloId}");

            $ponderaciones = $this->ponderacionModel
                ->obtenerPorCicloYParcial($cicloId, $parcial);

            log_message('debug', '📐 Ponderaciones: ' . json_encode($ponderaciones));

            // Estructura base
            $criterios = [];
            foreach ($ponderaciones as $p) {

                $crit = $this->db->table('criterios_evaluacion')
                    ->where('id', $p['criterio_id'])
                    ->get()->getRowArray();

                $criterios[$p['criterio_id']] = [
                    'nombre' => $crit['nombre'] ?? 'Criterio',
                    'porcentaje' => $p['porcentaje'],
                    'items' => []
                ];
            }

            log_message('debug', '🧱 Criterios armados: ' . json_encode($criterios));

            // 2) ITEMS: tareas
            $tareas = $this->db->table('tareas')
                ->select('id, titulo, criterio_id, porcentaje_tarea')
                ->where('parcial_numero', $parcial)
                ->where('asignacion_id', $asignacionId)
                ->get()->getResultArray();

            log_message('debug', '📌 Tareas encontradas: ' . json_encode($tareas));

            foreach ($tareas as $t) {
                if (isset($criterios[$t['criterio_id']])) {
                    $criterios[$t['criterio_id']]['items'][] = [
                        'id' => "t_" . $t['id'],
                        'tipo' => 'tarea',
                        'titulo' => $t['titulo'],
                        'porcentaje' => $t['porcentaje_tarea'] ?? null
                    ];
                }
            }

            // 3) ITEMS: proyectos
            $proyectos = $this->db->table('proyectos')
                ->select('id, titulo, criterio_id, porcentaje_proyecto')
                ->where('parcial_numero', $parcial)
                ->where('asignacion_id', $asignacionId)
                ->get()->getResultArray();

            log_message('debug', '📁 Proyectos encontrados: ' . json_encode($proyectos));

            foreach ($proyectos as $p) {
                if (isset($criterios[$p['criterio_id']])) {
                    $criterios[$p['criterio_id']]['items'][] = [
                        'id' => "p_" . $p['id'],
                        'tipo' => 'proyecto',
                        'titulo' => $p['titulo'],
                        'porcentaje' => $p['porcentaje_proyecto'] ?? null
                    ];
                }
            }

            // 4) ITEMS: exámenes
            $examenes = $this->db->table('examenes')
                ->select('id, titulo, criterio_id')
                ->where('parcial_num', $parcial)
                ->where('asignacion_id', $asignacionId)
                ->get()->getResultArray();

            log_message('debug', '🧪 Examenes encontrados: ' . json_encode($examenes));

            foreach ($examenes as $e) {
                if (isset($criterios[$e['criterio_id']])) {
                    $criterios[$e['criterio_id']]['items'][] = [
                        'id' => "e_" . $e['id'],
                        'tipo' => 'examen',
                        'titulo' => $e['titulo'],
                        'porcentaje' => $e['porcentaje_examen'] ?? ($criterios[$e['criterio_id']]['porcentaje'])
                    ];
                }
            }

            log_message('debug', '🧱 Criterios + items final: ' . json_encode($criterios));

            // =====================================================
            // 5) CALIFICACIONES REALES DE LOS ÍTEMS
            // =====================================================
            $cicloParcial = model('CicloParcialModel')
                ->where('ciclo_id', $cicloId)
                ->where('numero_parcial', $parcial)
                ->first();

            log_message('debug', '📆 CicloParcial: ' . json_encode($cicloParcial));
            $mgaModel = model('MateriaGrupoAlumnoModel');
            $alumnos = $mgaModel->obtenerAlumnosPorAsignacion($asignacionId);
            $alumnos = array_filter(
                $alumnos,
                fn($a) =>
                isset($a['alumno_id']) && $a['alumno_id'] !== null
            );

            $alumnos = array_values($alumnos); // *** corrección ***


            if (!$cicloParcial) {
                log_message('error', "❌ No se encontró ciclo_parcial para ciclo {$cicloId} y parcial {$parcial}");
                return $this->response->setJSON(['error' => 'Ciclo parcial no configurado']);
            }

            $cicloParcialId = $cicloParcial['id'];
            $raw = [];

            // tareas
            $raw = array_merge(
                $raw,
                $this->db->table('tareas_entregas te')
                    ->select("alumno_id, calificacion, CONCAT('t_', tarea_id) AS item_id")
                    ->join('tareas t', 't.id = te.tarea_id')
                    ->where('t.parcial_numero', $parcial)
                    ->where('t.asignacion_id', $asignacionId)
                    ->get()->getResultArray()
            );

            // proyectos
            $raw = array_merge(
                $raw,
                $this->db->table('proyectos_entregas pe')
                    ->select("alumno_id, calificacion, CONCAT('p_', proyecto_id) AS item_id")
                    ->join('proyectos p', 'p.id = pe.proyecto_id')
                    ->where('p.parcial_numero', $parcial)
                    ->where('p.asignacion_id', $asignacionId)
                    ->get()->getResultArray()
            );

            // exámenes
            $raw = array_merge(
                $raw,
                $this->db->table('examen_respuestas er')
                    ->select("alumno_id, calificacion, CONCAT('e_', examen_id) AS item_id")
                    ->join('examenes e', 'e.id = er.examen_id')
                    ->where('e.parcial_num', $parcial)
                    ->where('e.asignacion_id', $asignacionId)
                    ->get()->getResultArray()
            );

            log_message('debug', '📊 Raw calificaciones (entregas reales): ' . json_encode($raw));

            $calificaciones = [];
            foreach ($raw as $r) {
                $calificaciones[$r['alumno_id']][$r['item_id']] =
                    is_null($r['calificacion']) ? null : round($r['calificacion'] / 10);
            }

            log_message('debug', '📊 Calificaciones después de entregas: ' . json_encode($calificaciones));

            // =====================================================
            // 5.1) AGREGAR CALIFICACIONES PARCIALES YA GUARDADAS
            // =====================================================
            log_message('debug', '👥 Alumnos / MGA asignación: ' . json_encode($alumnos));

            $mgaValidos = array_column($alumnos, 'mga_id');
            log_message('debug', '✅ MGA válidos: ' . json_encode($mgaValidos));

            $parciales = [];
            if (!empty($mgaValidos)) {
                $parciales = $this->db->table('calificaciones_parcial')
                    ->where('ciclo_parcial_id', $cicloParcialId)
                    ->whereIn('materia_grupo_alumno_id', $mgaValidos)
                    ->get()
                    ->getResultArray();
            }

            log_message('debug', '📄 Parciales existentes: ' . json_encode($parciales));

            foreach ($parciales as $p) {

                $mga = $this->db->table('materia_grupo_alumno mga')
                    ->select('mga.id, ga.alumno_id')
                    ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
                    ->where('mga.id', $p['materia_grupo_alumno_id'])
                    ->get()->getRowArray();

                if (!$mga) {
                    log_message('error', '⚠ MGA no encontrado para id: ' . $p['materia_grupo_alumno_id']);
                    continue;
                }

                $alumnoId = $mga['alumno_id'];
                $itemId = $p['item_id'];
                $calif = intval($p['calificacion']);

                if (!isset($calificaciones[$alumnoId][$itemId])) {
                    $calificaciones[$alumnoId][$itemId] = $calif;
                }
            }

            log_message('debug', '📊 Calificaciones tras mezclar parciales: ' . json_encode($calificaciones));

            // =====================================================
            // 6) ASISTENCIA COMO ITEM
            // =====================================================

            $fechaInicio = $cicloParcial['fecha_inicio'];
            $fechaFin = $cicloParcial['fecha_fin'];

            $asistModel = model('AsistenciaModel');

            $criterioAsistenciaId = null;
            foreach ($criterios as $cid => $c) {
                if (stripos($c['nombre'], 'asist') !== false) {
                    $criterioAsistenciaId = $cid;
                    break;
                }
            }

            log_message('debug', '🟡 criterioAsistenciaId = ' . $criterioAsistenciaId);

            if ($criterioAsistenciaId !== null) {

                $criterios[$criterioAsistenciaId]['items'][] = [
                    'id' => 'asistencia_parcial',
                    'tipo' => 'asistencia',
                    'titulo' => 'Asistencia del parcial',
                    'porcentaje' => $criterios[$criterioAsistenciaId]['porcentaje']
                ];

                foreach ($alumnos as $al) {

                    if (empty($al['alumno_id'])) {
                        log_message('error', '🚫 alumno_id vacío en alumnos: ' . json_encode($al));
                        continue;
                    }
                    $res = $asistModel->obtenerResumenAsistencia(
                        $asignacionId,
                        $al['mga_id'],
                        $fechaInicio,
                        $fechaFin
                    );

                    log_message('debug', '📒 Resumen asistencia alumno ' . $al['alumno_id'] . ': ' . json_encode($res));

                    if ($res && $res['total_registros'] > 0) {
                        $valor = round(($res['asistencias'] / $res['total_registros']) * 10);
                    } else {
                        $valor = null;
                    }

                    $calificaciones[$al['alumno_id']]['asistencia_parcial'] = $valor;
                }
            }

            // =====================================================
            // 7) PARTICIPACIÓN COMO ITEM MANUAL
            // =====================================================

            $criterioParticipacionId = null;
            foreach ($criterios as $cid => $c) {
                if (stripos($c['nombre'], 'partic') !== false) {
                    $criterioParticipacionId = $cid;
                    break;
                }
            }

            log_message('debug', '🟢 criterioParticipacionId = ' . $criterioParticipacionId);

            if ($criterioParticipacionId !== null) {

                $criterios[$criterioParticipacionId]['items'][] = [
                    'id' => 'participacion_parcial',
                    'tipo' => 'participacion',
                    'titulo' => 'Participación',
                    'porcentaje' => $criterios[$criterioParticipacionId]['porcentaje']
                ];

                $califModel = model('CalificacionParcialModel');

                foreach ($alumnos as $al) {
                    $row = $califModel->where('materia_grupo_alumno_id', $al['mga_id'])
                        ->where('ciclo_parcial_id', $cicloParcialId)
                        ->where('criterio_id', $criterioParticipacionId)
                        ->where('item_id', 'participacion_parcial')
                        ->first();

                    log_message(
                        'debug',
                        "📗 Participacion previa alumno {$al['alumno_id']}: " . json_encode($row)
                    );

                    $calificaciones[$al['alumno_id']]['participacion_parcial'] =
                        $row ? intval($row['calificacion']) : "";
                }
            }

            // ORDEN correcto visual
            $ordenDeseado = [1, 2, 3, 4, 5];
            $criteriosOrdenados = [];

            foreach ($ordenDeseado as $cid) {
                if (isset($criterios[$cid])) {
                    $criteriosOrdenados[$cid] = $criterios[$cid];
                }
            }

            log_message('debug', '✅ Criterios ordenados: ' . json_encode($criteriosOrdenados));
            log_message('debug', '✅ Calificaciones finales: ' . json_encode($calificaciones));

            return $this->response->setJSON([
                'criterios' => $criteriosOrdenados,
                'calificaciones' => $calificaciones
            ]);

        } catch (\Throwable $e) {
            log_message('error', '❌ Error en obtenerValores(): ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());

            return $this->response
                ->setStatusCode(500)
                ->setJSON(['error' => 'Error interno al obtener valores']);
        }
    }

    public function guardarCalificaciones()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Método inválido']);
        }

        $data = $this->request->getJSON(true);

        log_message('debug', '📥 Datos recibidos: ' . json_encode($data));

        $asignacionId = $data['asignacion_id'];
        $cicloParcialId = $data['ciclo_parcial_id'];
        $items = $data['items'];

        $califParcialModel = model('CalificacionParcialModel');
        $tareaModel = model('EntregaTareaModel');
        $proyectoModel = model('EntregaProyectoModel');
        $examenModel = model('ExamenRespuestaModel');

        foreach ($items as $item) {

            log_message('debug', '🟦 Procesando ITEM: ' . json_encode($item));

            $alumnoId = intval($item['alumno_id']);

            // 🔥 Mantener SIEMPRE el prefijo
            $itemId = $item['item_id'];   // <-- ASÍ, sin convertir nada

            $tipo = $item['tipo'];
            $criterio = $item['criterio_id'];
            $valor = $item['calificacion'];


            // === OBTENER MGA ===
            $mga = $this->db->table('materia_grupo_alumno mga')
                ->select('mga.id AS mga_id, mga.grupo_materia_profesor_id, mga.grupo_alumno_id')
                ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
                ->where('ga.alumno_id', $alumnoId)
                ->where('mga.grupo_materia_profesor_id', $asignacionId)
                ->limit(1)
                ->get()
                ->getRowArray();


            log_message('debug', '🔎 MGA encontrado: ' . json_encode($mga));

            if (!$mga) {
                log_message('error', "❌ No se encontró MGA para alumno {$alumnoId}");
                continue;
            }

            $mgaId = $mga['mga_id'];


            // ======================================================
            // 🎯 GUARDAR EN TABLAS ORIGINALES (solo si aplica)
            // ======================================================
            try {

                switch ($tipo) {

                    case 'tarea':
                        $realId = intval(substr($itemId, 2)); // "t_5" → 5

                        $exist = $tareaModel
                            ->where('tarea_id', $realId)
                            ->where('alumno_id', $alumnoId)
                            ->first();

                        if ($exist) {
                            $tareaModel
                                ->where('tarea_id', $realId)
                                ->where('alumno_id', $alumnoId)
                                ->update(null, ['calificacion' => $valor * 10]);
                        } else {
                            $tareaModel->insert([
                                'tarea_id' => $realId,
                                'alumno_id' => $alumnoId,
                                'archivo' => null,
                                'fecha_entrega' => null,
                                'calificacion' => $valor * 10,
                                'retroalimentacion' => null
                            ]);
                        }
                        break;

                    case 'proyecto':
                        $realId = intval(substr($itemId, 2)); // "p_3" → 3

                        $exist = $proyectoModel
                            ->where('proyecto_id', $realId)
                            ->where('alumno_id', $alumnoId)
                            ->first();

                        if ($exist) {
                            $proyectoModel
                                ->where('proyecto_id', $realId)
                                ->where('alumno_id', $alumnoId)
                                ->update(null, ['calificacion' => $valor * 10]);
                        } else {
                            $proyectoModel->insert([
                                'proyecto_id' => $realId,
                                'alumno_id' => $alumnoId,
                                'archivo' => null,
                                'fecha_entrega' => null,
                                'calificacion' => $valor * 10,
                                'retroalimentacion' => null
                            ]);
                        }
                        break;

                    case 'examen':
                        $realId = intval(substr($itemId, 2)); // "e_2" → 2

                        $exist = $examenModel
                            ->where('examen_id', $realId)
                            ->where('alumno_id', $alumnoId)
                            ->first();

                        if ($exist) {
                            $examenModel
                                ->where('examen_id', $realId)
                                ->where('alumno_id', $alumnoId)
                                ->update(null, [
                                    'calificacion' => $valor * 10,
                                    'calificado' => 1
                                ]);
                        } else {
                            $examenModel->insert([
                                'examen_id' => $realId,
                                'alumno_id' => $alumnoId,
                                'calificacion' => $valor * 10,
                                'calificado' => 1
                            ]);
                        }
                        break;

                    // participación y asistencia NO tocan tablas originales
                }

            } catch (\Exception $e) {
                log_message('error', '❌ ERROR en tabla original: ' . $e->getMessage());
                return $this->response->setJSON(['error' => 'Error en tablas originales']);
            }


            // ======================================================
            // ⭐ GUARDAR EN calificaciones_parcial (UPSERT)
            // ======================================================
            try {
                $califParcialModel->guardarItem([
                    'materia_grupo_alumno_id' => $mgaId,
                    'ciclo_parcial_id' => $cicloParcialId,
                    'criterio_id' => $criterio,
                    'item_id' => $itemId,     // <-- PREFIJO TAL CUAL
                    'item_tipo' => $tipo,
                    'calificacion' => $valor
                ]);

            } catch (\Exception $e) {
                log_message('error', '❌ ERROR en calificaciones_parcial: ' . $e->getMessage());
                return $this->response->setJSON(['error' => 'Error en calificaciones_parcial']);
            }
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'msg' => 'Calificaciones guardadas correctamente.'
        ]);
    }

}
