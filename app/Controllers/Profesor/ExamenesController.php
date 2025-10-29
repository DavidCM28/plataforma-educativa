<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\ExamenModel;
use App\Models\ExamenPreguntaModel;
use App\Models\ExamenOpcionModel;
use App\Models\ExamenRespuestaModel;
use App\Models\ExamenRespuestaDetalleModel;

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
        $rows = $this->examenModel->obtenerPorAsignacion($asignacionId);
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

        $examenData = [
            'asignacion_id' => $asignacionId,
            'profesor_id' => $profesorId,
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

            $puntosTotales += (float) $fila['puntos'];

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
        $this->examenModel->update($id, ['estado' => 'publicado', 'fecha_publicacion' => date('Y-m-d H:i:s')]);
        return $this->response->setJSON(['success' => true, 'mensaje' => 'Examen publicado']);
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
        return view('lms/profesor/grupos/examen_editar', [
            'asignacionId' => $examen['asignacion_id'],
            'examen' => $examen
        ]);
    }

}
