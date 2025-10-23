<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\UsuarioModel;
use App\Models\UsuarioDetalleModel;
use App\Models\AsistenciaModel;

class Grupos extends BaseController
{
    public function index()
    {
        $usuario = session('usuario') ?? [];
        $profesorId = $usuario['id'] ?? session('id') ?? null;

        $grupoModel = new GrupoMateriaProfesorModel();

        // Usar un método que sí exista en tu modelo
        $asignaciones = $grupoModel->where('profesor_id', $profesorId)->findAll();

        return view('lms/profesor/grupos/listado', [
            'asignaciones' => $asignaciones
        ]);
    }

    public function ver($asignacionId)
    {
        $usuario = session('usuario') ?? [];
        $profesorId = $usuario['id'] ?? session('id') ?? null;

        $grupoModel = new GrupoMateriaProfesorModel();
        $grupo = $grupoModel->obtenerGrupoPorIdYProfesor($asignacionId, $profesorId);

        if (!$grupo) {
            return redirect()->to('/profesor/dashboard')->with('error', 'No tienes acceso a este grupo.');
        }

        // 🔹 Cargar alumnos asignados al grupo
        $mgaModel = new MateriaGrupoAlumnoModel();
        $alumnos = $mgaModel->obtenerAlumnosPorAsignacion($asignacionId);

        return view('lms/profesor/grupos/index', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'asignacionId' => $asignacionId, // 👈 SE AGREGA ESTA LÍNEA
            'tareas' => [],
            'calificaciones' => [],
        ]);
    }


    /**
     * 🔍 Ajax: Obtener detalles completos del alumno
     */
    public function detallesAlumno($id)
    {
        try {
            $usuarioModel = new UsuarioModel();
            $detalleModel = new UsuarioDetalleModel();

            $alumno = $usuarioModel->find($id);
            $detalles = $detalleModel->obtenerConUsuario($id);

            $db = \Config\Database::connect();

            // 🔹 Obtener datos académicos reales
            $academico = $db->table('alumno_carrera ac')
                ->select('
                carreras.nombre AS carrera,
                grupos.nombre AS grupo,
                grupos.turno AS turno,
                grupos.periodo AS periodo
            ')
                ->join('carreras', 'carreras.id = ac.carrera_id', 'left')
                ->join('grupo_alumno ga', 'ga.alumno_id = ac.alumno_id', 'left')
                ->join('grupos', 'grupos.id = ga.grupo_id', 'left')
                ->where('ac.alumno_id', $id)
                ->get()
                ->getRowArray();

            // 🔹 Extraer el "semestre" a partir del nombre del grupo (si tiene formato tipo IDS3M1)
            $semestre = null;
            if (!empty($academico['grupo'])) {
                // Buscar número dentro del nombre del grupo (ej. "IDS3M1" → 3)
                if (preg_match('/(\d+)/', $academico['grupo'], $coincidencia)) {
                    $semestre = $coincidencia[1];
                }
            }

            // Agregar el semestre calculado
            $academico['semestre'] = $semestre;

            return $this->response->setJSON([
                'usuario' => $alumno ?? [],
                'detalles' => $detalles ?? [],
                'academico' => $academico ?? []
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }

    public function asistencias($asignacionId)
    {
        $mgaModel = new MateriaGrupoAlumnoModel();
        $asistenciaModel = new AsistenciaModel();
        $grupoModel = new GrupoMateriaProfesorModel();

        $fecha = $this->request->getGet('fecha') ?? date('Y-m-d');
        $frecuencia = $this->request->getGet('frecuencia') ?? 1;

        $grupo = $grupoModel->find($asignacionId);
        $horarioTexto = trim($grupo['horario'] ?? '');
        $mapaDias = [];

        if (!empty($horarioTexto)) {
            $bloques = array_filter(array_map('trim', preg_split('/[;,]+/', $horarioTexto)));
            foreach ($bloques as $bloque) {
                if (preg_match('/^([A-ZÁÉÍÓÚÑ\-]+)\s+([0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2})$/u', $bloque, $m)) {
                    $dia = trim($m[1]);
                    $hora = trim($m[2]);
                    $mapaDias[$dia][] = $hora;
                }
            }
        }

        $diasCorto = ['D', 'L', 'M', 'X', 'J', 'V', 'S'];
        $diaSemana = $diasCorto[date('w', strtotime($fecha))] ?? '';

        $frecuencias = $mapaDias[$diaSemana] ?? [];
        $numFrecuencias = count($frecuencias);

        $alumnos = $mgaModel->obtenerAlumnosPorAsignacion($asignacionId);

        // 🧠 CORREGIDO: ahora sí filtra también por frecuencia
        $asistencias = $asistenciaModel
            ->select('a.*, u.matricula')
            ->from('asistencias a')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id', 'left')
            ->join('usuarios u', 'u.id = ga.alumno_id', 'left')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->where('a.fecha', $fecha)
            ->where('a.frecuencias', $frecuencia)
            ->findAll();

        // 🔹 Mapear asistencias existentes
        $estadoPorAlumno = [];
        foreach ($asistencias as $a) {
            $estadoPorAlumno[$a['matricula']] = $a;
        }

        // 🔹 Historial de fechas (todas las que existen en DB)
        $fechasRegistradas = $asistenciaModel
            ->select('DISTINCT(fecha)')
            ->join('materia_grupo_alumno mga', 'mga.id = asistencias.materia_grupo_alumno_id')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->orderBy('fecha', 'DESC')
            ->findAll();

        $mensaje = null;
        if (empty($frecuencias)) {
            $mensaje = "📅 No hay clases programadas este día.";
        } elseif (empty($asistencias) && $fecha !== date('Y-m-d')) {
            $mensaje = "⚠️ No hay asistencias registradas para esta fecha o frecuencia.";
        }

        return view('lms/profesor/grupos/asistencias', [
            'alumnos' => $alumnos,
            'fecha' => $fecha,
            'frecuenciaSeleccionada' => $frecuencia,
            'asignacionId' => $asignacionId,
            'asistencias' => $estadoPorAlumno,
            'fechasRegistradas' => $fechasRegistradas,
            'mensaje' => $mensaje,
            'frecuencias' => $frecuencias,
            'numFrecuencias' => $numFrecuencias,
            'diaSemana' => $diaSemana
        ]);
    }





    public function guardarAsistencias($asignacionId)
    {
        $asistenciaModel = new AsistenciaModel();

        $datos = $this->request->getPost('asistencias');
        $fecha = $this->request->getPost('fecha') ?? date('Y-m-d');
        $frecuencia = $this->request->getPost('frecuencias') ?? 1;

        log_message('debug', 'POST recibido: ' . json_encode($this->request->getPost()));

        if (!$datos) {
            return $this->response->setJSON(['error' => 'No se recibieron datos']);
        }

        try {
            $nuevos = 0;
            $actualizados = 0;

            foreach ($datos as $registro) {
                $mgaId = $registro['mga_id'];
                $estado = $registro['estado'];
                $observaciones = $registro['observaciones'] ?? null;

                // 🔍 Verificar si ya existe un registro con esta combinación
                $existente = $asistenciaModel
                    ->where('materia_grupo_alumno_id', $mgaId)
                    ->where('fecha', $fecha)
                    ->where('frecuencias', $frecuencia)
                    ->first();

                if ($existente) {
                    // 🔄 Actualizar si cambió algo
                    $asistenciaModel->update($existente['id'], [
                        'estado' => $estado,
                        'observaciones' => $observaciones,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    $actualizados++;
                } else {
                    // 🆕 Crear nuevo
                    $asistenciaModel->insert([
                        'materia_grupo_alumno_id' => $mgaId,
                        'fecha' => $fecha,
                        'estado' => $estado,
                        'frecuencias' => $frecuencia,
                        'observaciones' => $observaciones,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $nuevos++;
                }
            }

            $mensaje = "✅ $nuevos nuevas asistencias guardadas.";
            if ($actualizados > 0) {
                $mensaje .= " 🔄 $actualizados actualizadas.";
            }

            return $this->response->setJSON([
                'success' => true,
                'mensaje' => $mensaje,
                'nuevaFecha' => $fecha
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar asistencia: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => 'Error interno del servidor.',
                'detalles' => $e->getMessage()
            ]);
        }
    }

}