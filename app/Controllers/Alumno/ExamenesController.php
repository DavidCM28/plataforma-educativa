<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\ExamenModel;
use App\Models\PonderacionCicloModel;
use App\Models\ExamenRespuestaModel;
use App\Models\ExamenRespuestaDetalleModel;
use App\Models\ExamenPreguntaModel;

class ExamenesController extends BaseController
{
    protected $examenModel;

    public function __construct()
    {
        $this->examenModel = new ExamenModel();
    }

    // ================================
    // 📘 Vista parcial de exámenes
    // ================================
    public function index($asignacionId)
    {
        $examenes = $this->examenModel
            ->where('asignacion_id', $asignacionId)
            ->where('estado', 'publicado')
            ->orderBy('fecha_publicacion', 'DESC')
            ->findAll();

        return view('lms/alumno/materias/examenes', [
            'examenes' => $examenes
        ]);
    }

    // ================================
    // 📖 Resolver examen
    // ================================
    public function resolver($examenId)
    {
        $examen = $this->examenModel->obtenerConPreguntas($examenId);
        if (!$examen)
            return redirect()->back()->with('error', 'Examen no encontrado.');

        $ponderModel = new PonderacionCicloModel();
        $ponderacion = $ponderModel
            ->where('criterio_id', $examen['criterio_id'])
            ->where('parcial_num', $examen['parcial_num'])
            ->first();

        if ($ponderacion) {
            $examen['criterio_porcentaje'] = $ponderacion['porcentaje'];
        }

        // ✅ Estado: marcar como "en_progreso" al abrir el examen
        $alumnoId = session('id');
        $respModel = new ExamenRespuestaModel();
        $detModel = new ExamenRespuestaDetalleModel();

        $respuesta = $respModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($respuesta) {
            if ($respuesta['estado'] !== 'finalizado') {
                $respModel->update($respuesta['id'], ['estado' => 'en_progreso']);
            }
        } else {
            $respModel->insert([
                'examen_id' => $examenId,
                'alumno_id' => $alumnoId,
                'intento' => 1,
                'estado' => 'en_progreso', // 👈 nuevo
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'calificado' => 0,
                'calificacion' => null
            ]);
        }

        // ✅ Cargar respuestas previas
        $respuestasPrevias = [];
        $respuesta = $respModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($respuesta) {
            $detalles = $detModel
                ->where('respuesta_id', $respuesta['id'])
                ->findAll();

            foreach ($detalles as $d) {
                $respuestasPrevias["pregunta_{$d['pregunta_id']}"] =
                    $d['opcion_id'] ?? $d['respuesta_texto'];
            }
        }

        $examen['respuestas_previas'] = $respuestasPrevias;

        return view('lms/alumno/materias/examen_resolver', [
            'examen' => $examen
        ]);
    }

    // ================================
    // 💾 Guardar respuestas (autoguardado)
    // ================================
    public function guardarAlumno($examenId)
    {
        $method = strtolower($this->request->getMethod());
        log_message('error', '🎯 ENTRÓ A guardarAlumno con ID: ' . $examenId);

        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setBody('Método no permitido');
        }

        $alumnoId = session('id') ?? null;
        if (!$alumnoId) {
            return $this->response->setStatusCode(403)->setBody('No autorizado');
        }

        $examenModel = new ExamenModel();
        $examen = $examenModel->find($examenId);
        if (!$examen) {
            return $this->response->setStatusCode(404)->setBody('Examen no encontrado');
        }

        $respModel = new ExamenRespuestaModel();
        $detModel = new ExamenRespuestaDetalleModel();

        $respuesta = $respModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$respuesta) {
            $respuestaId = $respModel->insert([
                'examen_id' => $examenId,
                'alumno_id' => $alumnoId,
                'intento' => 1,
                'estado' => 'en_progreso', // 👈 nuevo
                'fecha_inicio' => date('Y-m-d H:i:s'),
                'calificado' => 0,
                'calificacion' => null
            ]);
        } else {
            $respuestaId = $respuesta['id'];

            // 👇 actualizar estado por si estaba "no_iniciado"
            if ($respuesta['estado'] === 'no_iniciado') {
                $respModel->update($respuestaId, ['estado' => 'en_progreso']);
            }
        }

        // 🔁 Guardar respuestas detalle
        $post = $this->request->getPost();
        foreach ($post as $key => $valor) {
            if (strpos($key, 'pregunta_') === 0) {
                $preguntaId = str_replace('pregunta_', '', $key);
                $isRadio = is_numeric($valor);

                $detalle = [
                    'respuesta_id' => $respuestaId,
                    'pregunta_id' => $preguntaId,
                    'opcion_id' => $isRadio ? $valor : null,
                    'respuesta_texto' => $isRadio ? null : $valor,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                $existente = $detModel
                    ->where('respuesta_id', $respuestaId)
                    ->where('pregunta_id', $preguntaId)
                    ->first();

                if ($existente)
                    $detModel->update($existente['id'], $detalle);
                else
                    $detModel->insert($detalle);
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Respuestas guardadas correctamente'
        ])->noCache();
    }

    // ================================
    // 🏁 Finalizar examen
    // ================================
    public function finalizarAlumno($examenId)
    {
        $alumnoId = session('id') ?? null;
        if (!$alumnoId)
            return $this->response->setStatusCode(403)->setBody('No autorizado');

        $respModel = new ExamenRespuestaModel();
        $respuesta = $respModel
            ->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->orderBy('id', 'DESC')
            ->first();

        if ($respuesta) {
            $respModel->update($respuesta['id'], [
                'fecha_fin' => date('Y-m-d H:i:s'),
                'estado' => 'finalizado' // 👈 nuevo
            ]);
        }

        return $this->response->setJSON(['status' => 'finalizado']);
    }
}
