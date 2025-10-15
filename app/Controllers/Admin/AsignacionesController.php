<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\MateriaModel;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\GrupoAlumnoModel;
use App\Models\CicloAcademicoModel;

class AsignacionesController extends BaseController
{
    protected $grupoModel;
    protected $materiaModel;
    protected $usuarioModel;
    protected $grupoMateriaProfesorModel;
    protected $grupoAlumnoModel;
    protected $cicloModel;
    protected $db;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->materiaModel = new MateriaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();
        $this->grupoAlumnoModel = new GrupoAlumnoModel();
        $this->cicloModel = new CicloAcademicoModel();
        $this->db = \Config\Database::connect();
    }

    /* =========================================================
       📘 Vista principal
       ========================================================= */
    public function index()
    {
        $data = [
            'grupos' => $this->grupoModel->orderBy('nombre', 'ASC')->findAll(),
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
        $horarioStr = implode('', $dias) . ' ' . $horaInicio . '-' . $horaFin;

        // Convertir a minutos para comparación
        $inicioMin = $this->horaToMinutos($horaInicio);
        $finMin = $this->horaToMinutos($horaFin);

        /* ======================================================
           🔍 Validar choques de horario
        ====================================================== */
        // 1️⃣ Revisar choques dentro del mismo grupo
        $choqueGrupo = $this->hayChoqueHorario($grupoId, $dias, $inicioMin, $finMin, 'grupo');

        // 2️⃣ Revisar choques del profesor en otros grupos
        $choqueProfesor = $this->hayChoqueHorario($profesorId, $dias, $inicioMin, $finMin, 'profesor');

        if ($choqueGrupo) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '🚫 Choque de horario detectado en el grupo (' . $choqueGrupo . ').'
            ]);
        }
        if ($choqueProfesor) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '🚫 El profesor ya tiene clase en ese horario (' . $choqueProfesor . ').'
            ]);
        }

        /* ======================================================
           ✅ Insertar asignación
        ====================================================== */
        $this->grupoMateriaProfesorModel->insert([
            'grupo_id' => $grupoId,
            'materia_id' => $materiaId,
            'profesor_id' => $profesorId,
            'ciclo' => $ciclo,
            'aula' => $aula,
            'horario' => $horarioStr
        ]);

        $asignacionId = $this->grupoMateriaProfesorModel->getInsertID();

        // Vincular alumnos del grupo
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
            'msg' => '✅ Profesor asignado correctamente sin choques de horario.'
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
       🕒 Horario visual del grupo
       ========================================================= */
    public function horarioGrupo($grupoId)
    {
        $asignaciones = $this->grupoMateriaProfesorModel
            ->select('materias.nombre AS materia, horario')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id')
            ->where('grupo_id', $grupoId)
            ->findAll();

        $resultado = [];
        foreach ($asignaciones as $a) {
            [$diasTxt, $rango] = explode(' ', $a['horario']);
            [$inicio, $fin] = explode('-', $rango);
            $resultado[] = [
                'materia' => $a['materia'],
                'dias' => str_split($diasTxt),
                'rango' => [
                    (int) str_replace(':', '', substr($inicio, 0, 5)),
                    (int) str_replace(':', '', substr($fin, 0, 5))
                ]
            ];
        }

        return $this->response->setJSON(['ok' => true, 'asignaciones' => $resultado]);
    }
}
