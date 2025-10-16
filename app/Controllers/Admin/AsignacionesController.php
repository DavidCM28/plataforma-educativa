<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\MateriaModel;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\GrupoAlumnoModel;
use App\Models\CicloAcademicoModel;
use App\Models\PlanMateriaModel;
use App\Models\PlanEstudioModel;
use App\Models\CarreraGrupoModel;


class AsignacionesController extends BaseController
{
    protected $grupoModel;
    protected $materiaModel;
    protected $usuarioModel;
    protected $grupoMateriaProfesorModel;
    protected $grupoAlumnoModel;
    protected $cicloModel;
    protected $planModel;
    protected $planMateriaModel;
    protected $carreraGrupoModel;
    protected $db;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->materiaModel = new MateriaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();
        $this->grupoAlumnoModel = new GrupoAlumnoModel();
        $this->cicloModel = new CicloAcademicoModel();
        $this->planModel = new PlanEstudioModel();
        $this->planMateriaModel = new PlanMateriaModel();
        $this->carreraGrupoModel = new CarreraGrupoModel();

        $this->db = \Config\Database::connect();
    }

    /* =========================================================
       📘 Vista principal
       ========================================================= */
    public function index()
    {
        $data = [
            'grupos' => $this->carreraGrupoModel->obtenerGruposCompletos(),
            'materias' => $this->materiaModel->where('activo', 1)->orderBy('nombre', 'ASC')->findAll(),
            'profesores' => $this->usuarioModel->where('rol_id', 3)->orderBy('nombre', 'ASC')->findAll(),
            'alumnos' => $this->usuarioModel->where('rol_id', 4)->orderBy('nombre', 'ASC')->findAll(),
            'ciclos' => $this->cicloModel->orderBy('id', 'DESC')->findAll(),
            'inscripciones' => $this->grupoAlumnoModel
                ->select('grupo_alumno.*, grupos.nombre as grupo, usuarios.nombre as alumno')
                ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
                ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
                ->findAll(),
        ];

        return view('lms/admin/asignaciones/index', $data);
    }


    /* =========================================================
       👨‍🏫 Asignar profesor a materia-grupo con validación de choques
       ========================================================= */
    public function asignarProfesor()
    {
        $grupoId = $this->request->getPost('grupo_id');
        $materiaId = $this->request->getPost('materia_id');
        $profesorId = $this->request->getPost('profesor_id');
        $ciclo = $this->request->getPost('ciclo');
        $aula = $this->request->getPost('aula');
        $dias = $this->request->getPost('dias') ?? [];
        $horaInicio = $this->request->getPost('hora_inicio');
        $horaFin = $this->request->getPost('hora_fin');

        if (!$grupoId || !$materiaId || !$profesorId || !$horaInicio || !$horaFin) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '⚠️ Todos los campos son obligatorios.'
            ]);
        }

        // Generar string de horario (ej. "LMX 07:30-09:10")
        $nuevoRango = implode('', $dias) . ' ' . $horaInicio . '-' . $horaFin;

        // Verificar si ya existe una asignación para ese grupo/materia/profesor
        $asignacionExistente = $this->grupoMateriaProfesorModel
            ->where('grupo_id', $grupoId)
            ->where('materia_id', $materiaId)
            ->where('profesor_id', $profesorId)
            ->first();

        if ($asignacionExistente) {
            // Agregar nuevo rango al final del horario existente
            $horarioActual = trim($asignacionExistente['horario']);
            $bloques = array_map('trim', explode(';', $horarioActual));

            // Evitar duplicados exactos
            if (!in_array($nuevoRango, $bloques)) {
                $horarioStr = $horarioActual . '; ' . $nuevoRango;
                $this->grupoMateriaProfesorModel->update($asignacionExistente['id'], [
                    'horario' => $horarioStr,
                ]);
            }

            $asignacionId = $asignacionExistente['id'];
        } else {
            // Crear nueva asignación
            $this->grupoMateriaProfesorModel->insert([
                'grupo_id' => $grupoId,
                'materia_id' => $materiaId,
                'profesor_id' => $profesorId,
                'ciclo' => $ciclo,
                'aula' => $aula,
                'horario' => $nuevoRango,
            ]);

            $asignacionId = $this->grupoMateriaProfesorModel->getInsertID();
        }

        // Vincular alumnos del grupo (solo si es nueva asignación)
        $alumnos = $this->grupoAlumnoModel->where('grupo_id', $grupoId)->findAll();
        foreach ($alumnos as $alumno) {
            $this->db->table('materia_grupo_alumno')->insert([
                'grupo_materia_profesor_id' => $asignacionId,
                'grupo_alumno_id' => $alumno['id'],
                'calificacion_final' => null,
                'asistencia' => 0,
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'msg' => '✅ Profesor asignado correctamente. Se agregó el horario sin duplicar.'
        ]);
    }


    /* =========================================================
       🧮 Función auxiliar: convertir hora ("HH:MM") → minutos
       ========================================================= */
    private function horaToMinutos(string $hora): int
    {
        [$h, $m] = explode(':', $hora);
        return (int) $h * 60 + (int) $m;
    }

    /* =========================================================
       ⚖️ Función auxiliar: validar choques de horario
       ========================================================= */
    private function hayChoqueHorario($id, $dias, $inicioMin, $finMin, $modo = 'grupo')
    {
        $builder = $this->grupoMateriaProfesorModel
            ->select('materias.nombre AS materia, grupo_materia_profesor.horario')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id');

        if ($modo === 'grupo') {
            $builder->where('grupo_id', $id);
        } else {
            $builder->where('profesor_id', $id);
        }

        $asignaciones = $builder->findAll();

        foreach ($asignaciones as $a) {
            [$diasTxt, $rango] = explode(' ', $a['horario']);
            [$hInicio, $hFin] = explode('-', $rango);

            $inicioExistente = $this->horaToMinutos($hInicio);
            $finExistente = $this->horaToMinutos($hFin);

            // Días compartidos
            $diasComunes = array_intersect(str_split($diasTxt), $dias);

            if (!empty($diasComunes)) {
                // Si hay traslape
                if ($inicioMin < $finExistente && $finMin > $inicioExistente) {
                    return $a['materia'] . ' (' . implode(',', $diasComunes) . ')';
                }
            }
        }

        return false;
    }

    /* =========================================================
       🎓 Asignar alumno al grupo
       ========================================================= */
    public function asignarAlumno()
    {
        $grupoId = $this->request->getPost('grupo_id');
        $alumnoId = $this->request->getPost('alumno_id');

        if (!$grupoId || !$alumnoId) {
            return redirect()->back()->with('msg', 'Faltan datos para inscribir.');
        }

        $this->grupoAlumnoModel->insert([
            'grupo_id' => $grupoId,
            'alumno_id' => $alumnoId,
            'fecha_inscripcion' => date('Y-m-d'),
            'estatus' => 'Inscrito'
        ]);

        $grupoAlumnoId = $this->grupoAlumnoModel->getInsertID();

        $asignaciones = $this->grupoMateriaProfesorModel->where('grupo_id', $grupoId)->findAll();

        foreach ($asignaciones as $asig) {
            $this->db->table('materia_grupo_alumno')->insert([
                'grupo_materia_profesor_id' => $asig['id'],
                'grupo_alumno_id' => $grupoAlumnoId,
                'calificacion_final' => null,
                'asistencia' => 0,
            ]);
        }

        return redirect()->back()->with('msg', 'Alumno inscrito correctamente en el grupo y materias.');
    }

    /* =========================================================
       🗑️ Eliminar profesor/asignación
       ========================================================= */
    public function eliminarProfesor($id)
    {
        $this->grupoMateriaProfesorModel->delete($id);
        return $this->response->setJSON(['ok' => true, 'msg' => 'Asignación eliminada correctamente.']);
    }

    /* =========================================================
       🗑️ Eliminar alumno del grupo
       ========================================================= */
    public function eliminarAlumno($id)
    {
        $this->grupoAlumnoModel->delete($id);
        return $this->response->setJSON(['ok' => true, 'msg' => 'Alumno eliminado correctamente.']);
    }

    /* =========================================================
   🕒 Horario visual del grupo (materia + profesor + horario)
   ========================================================= */
    public function horarioGrupo($grupoId)
    {
        $asignaciones = $this->grupoMateriaProfesorModel
            ->select('
            grupo_materia_profesor.id,
            grupo_materia_profesor.grupo_id,
            grupo_materia_profesor.materia_id,
            grupo_materia_profesor.profesor_id,
            grupo_materia_profesor.horario,
            materias.nombre AS materia,
            usuarios.nombre AS profesor
        ')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id', 'left')
            ->where('grupo_id', $grupoId)
            ->findAll();

        $resultado = [];

        foreach ($asignaciones as $a) {
            $bloques = explode(';', $a['horario']);
            foreach ($bloques as $bloque) {
                $bloque = trim($bloque);
                if (preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $bloque, $m)) {
                    $diasTxt = $m[1];
                    $inicio = $m[2];
                    $fin = $m[3];

                    $inicioNum = (int) str_replace(':', '', $inicio);
                    $finNum = (int) str_replace(':', '', $fin);

                    $resultado[] = [
                        'id' => $a['id'],
                        'grupo_id' => $a['grupo_id'],
                        'materia_id' => $a['materia_id'],
                        'profesor_id' => $a['profesor_id'],
                        'materia' => $a['materia'],
                        'profesor' => $a['profesor'] ?? 'Sin asignar',
                        'dias' => str_split($diasTxt),
                        'rango' => [$inicioNum, $finNum],
                        'inicio_str' => $inicio,
                        'fin_str' => $fin
                    ];
                }
            }
        }

        return $this->response->setJSON(['ok' => true, 'asignaciones' => $resultado]);
    }


    public function materiasPorGrupo($grupoId)
    {
        $carreraGrupoModel = new CarreraGrupoModel();

        // 1️⃣ Obtener la carrera asociada al grupo
        $relacion = $carreraGrupoModel
            ->select('carrera_grupo.carrera_id, grupos.periodo')
            ->join('grupos', 'grupos.id = carrera_grupo.grupo_id')
            ->where('carrera_grupo.grupo_id', $grupoId)
            ->first();

        if (!$relacion) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '❌ No se encontró la relación del grupo con una carrera.'
            ]);
        }

        $carreraId = $relacion['carrera_id'];
        $periodo = $relacion['periodo']; // esto es tu "ciclo actual" o cuatrimestre

        // 2️⃣ Buscar el plan activo de la carrera
        $plan = $this->planModel
            ->where('carrera_id', $carreraId)
            ->where('activo', 1)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$plan) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '⚠️ No se encontró un plan activo para la carrera seleccionada.'
            ]);
        }

        // 3️⃣ Buscar materias del plan que correspondan al periodo del grupo
        $materias = $this->planMateriaModel
            ->select('materias.id, materias.nombre, plan_materias.cuatrimestre, plan_materias.tipo')
            ->join('materias', 'materias.id = plan_materias.materia_id')
            ->where('plan_materias.plan_id', $plan['id'])
            ->where('plan_materias.cuatrimestre', $periodo)
            ->where('materias.activo', 1)
            ->orderBy('materias.nombre', 'ASC')
            ->findAll();

        if (!$materias) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '⚠️ No hay materias registradas para el ciclo ' . $periodo
            ]);
        }

        // 4️⃣ Respuesta final
        return $this->response->setJSON([
            'ok' => true,
            'materias' => $materias
        ]);
    }

    /* =========================================================
       ✏️ Actualizar asignación existente
       ========================================================= */
    public function actualizarAsignacion($id)
    {
        $asignacion = $this->grupoMateriaProfesorModel->find($id);

        if (!$asignacion) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '❌ Asignación no encontrada.'
            ]);
        }

        $grupoId = $asignacion['grupo_id'];
        $materiaId = $asignacion['materia_id'];

        $nuevoProfesor = $this->request->getPost('profesor_id');
        $aula = $this->request->getPost('aula');
        $dias = $this->request->getPost('dias') ?? [];
        $horaInicio = $this->request->getPost('hora_inicio');
        $horaFin = $this->request->getPost('hora_fin');

        $nuevoRango = implode('', $dias) . ' ' . $horaInicio . '-' . $horaFin;

        // 🔹 Actualiza el profesor en todas las asignaciones del mismo grupo/materia
        $this->grupoMateriaProfesorModel
            ->where('grupo_id', $grupoId)
            ->where('materia_id', $materiaId)
            ->set(['profesor_id' => $nuevoProfesor])
            ->update();

        // 🔹 Actualiza el horario y aula de la asignación actual
        $this->grupoMateriaProfesorModel->update($id, [
            'horario' => $nuevoRango,
            'aula' => $aula,
        ]);

        return $this->response->setJSON([
            'ok' => true,
            'msg' => '✏️ Profesor actualizado en todas las asignaciones de esta materia.'
        ]);
    }


    public function detalle($id)
    {
        $asignacion = $this->grupoMateriaProfesorModel
            ->select('grupo_materia_profesor.*, materias.nombre AS materia_nombre, usuarios.nombre AS profesor_nombre')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id', 'left')
            ->find($id);

        if (!$asignacion) {
            return $this->response->setJSON(['ok' => false]);
        }

        // ======================================================
        // 🔍 Extraer todos los bloques de horario ("LMX 10:00-10:50; J 12:30-13:20")
        // ======================================================
        $bloques = [];
        $horarios = array_map('trim', explode(';', $asignacion['horario'] ?? ''));

        foreach ($horarios as $bloque) {
            if (preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', trim($bloque), $m)) {
                $bloques[] = [
                    'dias' => str_split($m[1]),
                    'hora_inicio' => $m[2],
                    'hora_fin' => $m[3],
                    'texto' => trim($bloque)
                ];
            }
        }

        // Si hay al menos un bloque, tomamos el primero como principal (para edición rápida)
        $primerBloque = $bloques[0] ?? [
            'dias' => [],
            'hora_inicio' => '',
            'hora_fin' => ''
        ];

        // Agregar campos adicionales útiles para el frontend
        $asignacion['bloques'] = $bloques;
        $asignacion['dias'] = $primerBloque['dias'];
        $asignacion['hora_inicio'] = $primerBloque['hora_inicio'];
        $asignacion['hora_fin'] = $primerBloque['hora_fin'];

        return $this->response->setJSON([
            'ok' => true,
            'asignacion' => $asignacion
        ]);
    }



}
