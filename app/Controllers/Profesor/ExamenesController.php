<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\ExamenModel;
use App\Models\ExamenPreguntaModel;
use App\Models\ExamenOpcionModel;
use App\Models\ExamenRespuestaModel;
use App\Models\ExamenRespuestaDetalleModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\CriterioEvaluacionModel;
use App\Models\PonderacionCicloModel;
use \App\Models\MateriaGrupoAlumnoModel;

class ExamenesController extends BaseController
{
    protected $examenModel;
    protected $preguntaModel;
    protected $opcionModel;
    protected $respuestaModel;
    protected $detalleModel;

    public function __construct()
    {
        $this->examenModel = new ExamenModel();
        $this->preguntaModel = new ExamenPreguntaModel();
        $this->opcionModel = new ExamenOpcionModel();
        $this->respuestaModel = new ExamenRespuestaModel();
        $this->detalleModel = new ExamenRespuestaDetalleModel();
    }

    // ============================
    // Vista parcial (se carga en la pestaña)
    // ============================
    public function index($asignacionId)
    {
        return view('lms/profesor/grupos/examenes', [
            'asignacionId' => $asignacionId
        ]);
    }

    // ============================
    // Listar exámenes de un grupo
    // ============================
    public function listar($asignacionId)
    {
        $rows = $this->examenModel->obtenerPorAsignacionConPorcentaje($asignacionId);
        return $this->response->setJSON($rows);
    }


    // ============================
    // Detalle con preguntas/opciones
    // ============================
    public function detalle($id)
    {
        $examen = $this->examenModel->obtenerConPreguntas($id);
        if (!$examen)
            return $this->response->setJSON(['error' => 'Examen no encontrado.']);
        return $this->response->setJSON($examen);
    }

    // ============================
    // Crear / Editar Examen + preguntas
    // Body esperado:
    //  - datos examen + arreglo preguntas [{id?, tipo, pregunta, puntos, orden, imagen? (file)}, opciones[]]
    // ============================
    public function guardar()
    {
        $data = $this->request->getPost();
        $files = $this->request->getFiles();

        $examenId = $data['id'] ?? null;
        $asignacionId = $data['asignacion_id'] ?? null;
        $profesorId = session('id') ?? session('usuario_id') ?? null;

        if (!$asignacionId || empty($data['titulo'])) {
            return $this->response->setJSON(['error' => 'Título y asignación son obligatorios.']);
        }

        // 🧭 Buscar ciclo del grupo
        $asignacionModel = new GrupoMateriaProfesorModel();
        $asignacion = $asignacionModel->find($asignacionId);
        $cicloId = $asignacion['ciclo_id'] ?? null;

        // 🧮 Determinar parcial (manual o primero por defecto)
        $parcialNum = $data['parcial_num'] ?? 1;

        // 🧾 Determinar criterio: buscar en ponderaciones_ciclo el criterio "Examen"
        $pondModel = new PonderacionCicloModel();
        $criterio = $pondModel
            ->select('ponderaciones_ciclo.criterio_id')
            ->join('criterios_evaluacion', 'criterios_evaluacion.id = ponderaciones_ciclo.criterio_id')
            ->where('ponderaciones_ciclo.ciclo_id', $cicloId)
            ->where('ponderaciones_ciclo.parcial_num', $parcialNum)
            ->like('criterios_evaluacion.nombre', 'examen', 'both')
            ->first();

        $criterioId = $criterio['criterio_id'] ?? null;

        // 📋 Preparar datos del examen
        $examenData = [
            'asignacion_id' => $asignacionId,
            'profesor_id' => $profesorId,
            'parcial_num' => $parcialNum,
            'criterio_id' => $criterioId,
            'titulo' => trim($data['titulo']),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'instrucciones' => trim($data['instrucciones'] ?? ''),
            'tiempo_minutos' => !empty($data['tiempo_minutos']) ? (int) $data['tiempo_minutos'] : null,
            'intentos_maximos' => !empty($data['intentos_maximos']) ? (int) $data['intentos_maximos'] : null,
            'fecha_publicacion' => !empty($data['fecha_publicacion']) ? date('Y-m-d H:i:s', strtotime($data['fecha_publicacion'])) : null,
            'fecha_cierre' => !empty($data['fecha_cierre']) ? date('Y-m-d H:i:s', strtotime($data['fecha_cierre'])) : null,
            'estado' => $data['estado'] ?? 'borrador',
        ];

        // Crear / actualizar examen
        if ($examenId) {
            $this->examenModel->update($examenId, $examenData);
        } else {
            $examenId = $this->examenModel->insert($examenData);
        }

        // Puntos totales se recalculan al final
        $puntosTotales = 0;

        // Preguntas (vienen en JSON)
        $jsonPreguntas = $data['preguntas'] ?? '[]';
        $preguntas = json_decode($jsonPreguntas, true) ?? [];

        // Limpieza si se envía save completo (opcional): podrías borrar preguntas/ops previas y recrearlas
        // Aquí usaremos "upsert": si trae id actualizamos; si no, insertamos.
        foreach ($preguntas as $idx => $p) {
            $pregId = $p['id'] ?? null;
            $fila = [
                'examen_id' => $examenId,
                'tipo' => $p['tipo'] === 'abierta' ? 'abierta' : 'opcion',
                'pregunta' => $p['pregunta'],
                'puntos' => (float) ($p['puntos'] ?? 1),
                'es_extra' => isset($p['es_extra']) && (int) $p['es_extra'] === 1 ? 1 : 0,
                'orden' => (int) ($p['orden'] ?? ($idx + 1)),
            ];


            // Imagen (si se subió)
            $keyImg = "pregunta_imagen_{$idx}";
            if (!empty($files[$keyImg]) && $files[$keyImg]->isValid() && !$files[$keyImg]->hasMoved()) {
                $newName = $files[$keyImg]->getRandomName();
                $files[$keyImg]->move(FCPATH . 'uploads/examenes', $newName);
                $fila['imagen'] = $newName;
            }

            if ($pregId) {
                $this->preguntaModel->update($pregId, $fila);
            } else {
                $pregId = $this->preguntaModel->insert($fila);
            }

            if (empty($p['extra']) || !$p['extra']) {
                $puntosTotales += (float) $fila['puntos'];
            }


            // Opciones si es de opción múltiple
            if ($fila['tipo'] === 'opcion') {
                // Limpiado básico: elimina opciones previas y re-inserta (simple y seguro)
                $this->opcionModel->where('pregunta_id', $pregId)->delete();

                $ops = $p['opciones'] ?? [];
                foreach ($ops as $j => $op) {
                    $this->opcionModel->insert([
                        'pregunta_id' => $pregId,
                        'texto' => $op['texto'],
                        'es_correcta' => !empty($op['es_correcta']) ? 1 : 0,
                        'orden' => (int) ($op['orden'] ?? ($j + 1)),
                    ]);
                }
            } else {
                // abiertas: no hay opciones
                $this->opcionModel->where('pregunta_id', $pregId)->delete();
            }
        }

        // Actualizar puntos totales
        $this->examenModel->update($examenId, ['puntos_totales' => $puntosTotales]);

        return $this->response->setJSON(['success' => true, 'mensaje' => 'Examen guardado', 'id' => $examenId]);
    }

    // ============================
    // Eliminar examen
    // ============================
    public function eliminar($id)
    {
        // Eliminación en cascada por FKs
        $this->examenModel->delete($id);
        return $this->response->setJSON(['success' => true, 'mensaje' => 'Examen eliminado']);
    }

    // ============================
    // Publicar / Cerrar
    // ============================
    public function publicar($id)
    {
        $this->examenModel->update($id, [
            'estado' => 'publicado',
            'fecha_publicacion' => date('Y-m-d H:i:s')
        ]);

        // ✅ Registrar automáticamente a los alumnos
        $examen = $this->examenModel->find($id);
        if ($examen && !empty($examen['asignacion_id'])) {
            $this->registrarAlumnosPendientes($examen['asignacion_id'], $id);
        }

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Examen publicado y registros iniciales creados'
        ]);
    }

    private function registrarAlumnosPendientes($asignacionId, $examenId)
    {
        $mgaModel = new MateriaGrupoAlumnoModel();
        $respuestaModel = new ExamenRespuestaModel();

        $alumnos = $mgaModel->obtenerAlumnosPorAsignacion($asignacionId);

        foreach ($alumnos as $alumno) {
            // Verificar si ya existe un registro (por ejemplo, si el examen fue republicado)
            $existe = $respuestaModel
                ->where('examen_id', $examenId)
                ->where('alumno_id', $alumno['alumno_id'])
                ->first();

            if (!$existe) {
                $respuestaModel->insert([
                    'examen_id' => $examenId,
                    'alumno_id' => $alumno['alumno_id'],
                    'intento' => 0,
                    'estado' => 'no_iniciado',
                    'calificado' => 0,
                    'calificacion' => null,
                    'fecha_inicio' => null,
                    'fecha_fin' => null,
                ]);
            }
        }
    }

    public function cerrar($id)
    {
        $this->examenModel->update($id, ['estado' => 'cerrado', 'fecha_cierre' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true, 'mensaje' => 'Examen cerrado']);
    }

    // ============================
    // Resumen y respuestas (para profesor)
    // ============================
    public function resumen($examenId)
    {
        // Aquí puedes cruzar con tu lista de alumnos del grupo para “quién lo resolvió y quién no”.
        // Para este MVP devolvemos conteos simples.
        $totalRespuestas = $this->respuestaModel->where('examen_id', $examenId)->countAllResults();
        return $this->response->setJSON([
            'examen_id' => (int) $examenId,
            'respuestas' => $totalRespuestas
        ]);
    }

    public function listarRespuestas($examenId)
    {
        $rows = $this->respuestaModel->where('examen_id', $examenId)->orderBy('id', 'DESC')->findAll();
        return $this->response->setJSON($rows);
    }

    // ============================
    // Calificar manual una respuesta (p.ej. preguntas abiertas)
    // body: calificacion (decimal), calificado=1
    // ============================
    public function calificarRespuesta($respuestaId)
    {
        $calificacion = (float) ($this->request->getPost('calificacion') ?? 0);
        $this->respuestaModel->update($respuestaId, ['calificacion' => $calificacion, 'calificado' => 1]);
        return $this->response->setJSON(['success' => true, 'mensaje' => 'Respuesta calificada']);
    }

    public function crear($asignacionId)
    {
        return view('lms/profesor/grupos/examen_editar', [
            'asignacionId' => $asignacionId,
            'examen' => null
        ]);
    }

    public function editar($id)
    {
        $examen = $this->examenModel->obtenerConPreguntas($id);

        // 🔎 Buscar porcentaje del criterio si existe
        $criterioPorcentaje = null;
        if (!empty($examen['criterio_id'])) {
            $db = \Config\Database::connect();
            $criterioRow = $db->table('ponderaciones_ciclo')
                ->select('porcentaje')
                ->where('criterio_id', $examen['criterio_id'])
                ->where('parcial_num', $examen['parcial_num'])
                ->get()
                ->getRowArray();
            $criterioPorcentaje = $criterioRow['porcentaje'] ?? null;
        }

        return view('lms/profesor/grupos/examen_editar', [
            'asignacionId' => $examen['asignacion_id'],
            'examen' => $examen,
            'criterioPorcentaje' => $criterioPorcentaje
        ]);
    }

    // ============================
// Eliminar una pregunta individual
// ============================
    public function eliminarPregunta($id)
    {
        $pregunta = $this->preguntaModel->find($id);
        if (!$pregunta) {
            return $this->response->setJSON(['success' => false, 'error' => 'Pregunta no encontrada']);
        }

        // 🔥 Eliminar opciones asociadas y la pregunta
        $this->opcionModel->where('pregunta_id', $id)->delete();
        $this->preguntaModel->delete($id);

        return $this->response->setJSON(['success' => true, 'mensaje' => 'Pregunta eliminada']);
    }

    public function verRespuestas($examenId)
    {
        $examenModel = new ExamenModel();
        $pregModel = new ExamenPreguntaModel();
        $detModel = new ExamenRespuestaDetalleModel();
        $respModel = new ExamenRespuestaModel();
        $asigModel = new GrupoMateriaProfesorModel();
        $mgaModel = new MateriaGrupoAlumnoModel();

        $examen = $examenModel->obtenerConPreguntas($examenId);
        if (!$examen) {
            return redirect()->back()->with('error', 'Examen no encontrado.');
        }

        $asignacionId = $examen['asignacion_id'];

        // Lista de alumnos de la materia
        $alumnos = $mgaModel->obtenerAlumnosPorAsignacion($asignacionId);

        // Para cada alumno, obtener su registro de respuestas (si existe)
        foreach ($alumnos as &$a) {
            $respuesta = $respModel
                ->where('examen_id', $examenId)
                ->where('alumno_id', $a['alumno_id'])
                ->orderBy("FIELD(estado,'finalizado','en_progreso','no_iniciado')", '', false)
                ->orderBy('id', 'DESC')
                ->first();


            $a['respuesta'] = $respuesta;

            if ($respuesta) {
                $detalles = $detModel
                    ->where('respuesta_id', $respuesta['id'])
                    ->findAll();

                // indexar por pregunta
                $map = [];
                foreach ($detalles as $d) {
                    $map[$d['pregunta_id']] = $d;
                }
                $a['detalles'] = $map;
            } else {
                $a['detalles'] = [];
            }
        }

        // Ordenar por estado: finalizado -> en_progreso -> no_iniciado
        usort($alumnos, function ($a, $b) {

            $orden = [
                'finalizado' => 1,
                'en_progreso' => 2,
                'no_iniciado' => 3
            ];

            $estadoA = $a['respuesta']['estado'] ?? 'no_iniciado';
            $estadoB = $b['respuesta']['estado'] ?? 'no_iniciado';

            return $orden[$estadoA] <=> $orden[$estadoB];
        });

        return view('lms/profesor/grupos/examen_respuestas_profesor', [
            'examen' => $examen,
            'alumnos' => $alumnos,
            'asignacionId' => $asignacionId,
        ]);
    }


    public function detalleRespuesta($examenId, $alumnoId)
    {
        // Obtener examen con todas sus preguntas y opciones
        $examen = $this->examenModel->obtenerConPreguntas($examenId);
        if (!$examen) {
            return redirect()->back()->with('error', 'Examen no encontrado.');
        }

        $respModel = new ExamenRespuestaModel();
        $detModel = new ExamenRespuestaDetalleModel();
        $opModel = new ExamenOpcionModel();

        // Registro principal del alumno en este examen
        $respuesta = $respModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->first();

        if (!$respuesta) {
            return redirect()->back()->with('error', 'El alumno no tiene registro en este examen.');
        }

        // Cargar detalles por pregunta
        $detalles = $detModel
            ->where('respuesta_id', $respuesta['id'])
            ->findAll();

        // ==============================
        //  AUTOCALIFICAR OPCIÓN MÚLTIPLE
        // ==============================
        foreach ($detalles as &$d) {

            // Buscar la pregunta asociada
            $pregunta = null;
            foreach ($examen['preguntas'] as $p) {
                if ($p['id'] == $d['pregunta_id']) {
                    $pregunta = $p;
                    break;
                }
            }

            if ($pregunta) {
                $d['pregunta'] = $pregunta['pregunta'];
                $d['tipo'] = $pregunta['tipo'];
                $d['puntos'] = $pregunta['puntos'];
                $d['imagen'] = $pregunta['imagen'] ?? null;
            }

            // Si es opción múltiple, autocalificar
            if ($pregunta && $pregunta['tipo'] === 'opcion' && $d['opcion_id']) {

                $opcion = $opModel->find($d['opcion_id']);

                if ($opcion && $opcion['es_correcta']) {
                    $d['puntos_obtenidos'] = $pregunta['puntos'];
                } else {
                    $d['puntos_obtenidos'] = 0;
                }

                // Guardar calificación en BD
                $detModel->update($d['id'], [
                    'puntos_obtenidos' => $d['puntos_obtenidos']
                ]);

                // Agregar texto de la opción elegida
                $d['opcion_texto'] = $opcion['texto'] ?? null;
            }
        }

        // ============================
        //  Datos del alumno para vista
        // ============================
        $db = \Config\Database::connect();
        $alumno = $db->table('usuarios')
            ->where('id', $alumnoId)
            ->get()
            ->getRowArray();

        if ($alumno) {
            $respuesta['alumno_nombre'] = $alumno['nombre'] . ' ' . $alumno['apellido_paterno'];
            $respuesta['matricula'] = $alumno['matricula'];
        }

        // ============================
        //  Enviar a la vista final
        // ============================
        return view('lms/profesor/grupos/examen_respuesta_detalle', [
            'examen' => $examen,
            'respuesta' => $respuesta,
            'detalles' => $detalles
        ]);
    }


    public function calificarDetalle()
    {
        $detalleId = $this->request->getPost('detalle_id');
        $puntos = $this->request->getPost('puntos');
        $observacion = $this->request->getPost('observacion') ?? '';

        if (!$detalleId) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'ID inválido'
            ]);
        }

        $detModel = new ExamenRespuestaDetalleModel();
        $respModel = new ExamenRespuestaModel();

        // ===========================
        // 1. Actualizar el detalle
        // ===========================
        $detModel->update($detalleId, [
            'puntos_obtenidos' => $puntos,
            'observacion' => $observacion
        ]);

        // ===========================
        // 2. Obtener el detalle actualizado
        // ===========================
        $detalle = $detModel->find($detalleId);

        if (!$detalle) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Detalle no encontrado'
            ]);
        }

        $respuestaId = $detalle['respuesta_id'];

        // ===========================
        // 3. Recalcular la calificación total
        // ===========================
        $sum = $detModel
            ->selectSum('puntos_obtenidos')
            ->where('respuesta_id', $respuestaId)
            ->first();

        $total = $sum['puntos_obtenidos'] ?? 0;

        // ===========================
        // 4. Guardar calificación total en examen_respuestas
        // ===========================
        $respModel->update($respuestaId, [
            'calificacion' => $total,
            'calificado' => 1
        ]);

        return $this->response->setJSON([
            'success' => true,
            'total' => $total
        ]);
    }


    public function apiAlumno($examenId, $alumnoId)
    {
        $examen = $this->examenModel->obtenerConPreguntas($examenId);
        if (!$examen) {
            return $this->response->setJSON(['error' => 'Examen no encontrado']);
        }

        $resp = $this->respuestaModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->first();

        if (!$resp) {
            return $this->response->setJSON(['error' => 'El alumno no ha abierto el examen']);
        }

        $detalles = $this->detalleModel
            ->where('respuesta_id', $resp['id'])
            ->findAll();

        // mapa rápido por pregunta
        $map = [];
        foreach ($detalles as $d) {
            $map[$d['pregunta_id']] = $d;
        }

        $listaPreguntas = [];
        foreach ($examen['preguntas'] as $p) {
            $p['detalle'] = $map[$p['id']] ?? null;

            // mostrar texto de opción elegida
            if ($p['tipo'] === 'opcion' && $p['detalle'] && $p['detalle']['opcion_id']) {
                $op = model(ExamenOpcionModel::class)->find($p['detalle']['opcion_id']);
                $p['detalle']['opcion_texto'] = $op['texto'] ?? null;
            }

            $listaPreguntas[] = $p;
        }

        // Obtener datos del alumno
        $db = \Config\Database::connect();
        $u = $db->table('usuarios')
            ->where('id', $alumnoId)
            ->get()
            ->getRowArray();

        return $this->response->setJSON([
            'nombre' => $u['nombre'] . ' ' . $u['apellido_paterno'],
            'matricula' => $u['matricula'],
            'preguntas' => $listaPreguntas,
        ]);
    }
    public function calificarDetalleMultiple()
    {
        $json = $this->request->getPost('detalles');
        $items = json_decode($json, true);

        if (!$items) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Datos inválidos'
            ]);
        }

        $detModel = new ExamenRespuestaDetalleModel();
        $respModel = new ExamenRespuestaModel();

        $respuestaId = null;

        foreach ($items as $d) {
            $det = $detModel->find($d['id']);
            if (!$det)
                continue;

            $respuestaId = $det['respuesta_id'];

            $detModel->update($d['id'], [
                'puntos_obtenidos' => $d['puntos'],
                'observacion' => $d['observacion']
            ]);
        }

        if ($respuestaId) {
            $sum = $detModel
                ->selectSum('puntos_obtenidos')
                ->where('respuesta_id', $respuestaId)
                ->first();

            $total = $sum['puntos_obtenidos'] ?? 0;

            $respModel->update($respuestaId, [
                'calificacion' => $total,
                'calificado' => 1
            ]);

            return $this->response->setJSON([
                'success' => true,
                'total' => $total
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error' => 'No se pudo guardar'
        ]);
    }

}
