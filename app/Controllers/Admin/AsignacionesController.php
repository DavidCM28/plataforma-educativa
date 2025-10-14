<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\MateriaModel;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\GrupoAlumnoModel;
use App\Models\CicloAcademicoModel;
use CodeIgniter\Database\Config;

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

    public function index()
    {
        $data = [
            'grupos' => $this->grupoModel->findAll(),
            'materias' => $this->materiaModel->where('activo', 1)->findAll(),
            'profesores' => $this->usuarioModel->where('rol_id', 3)->findAll(),
            'alumnos' => $this->usuarioModel->where('rol_id', 4)->findAll(),
            'ciclos' => $this->cicloModel->orderBy('id', 'ASC')->findAll(),
            'asignaciones' => $this->grupoMateriaProfesorModel
                ->select('grupo_materia_profesor.*, grupos.nombre as grupo, materias.nombre as materia, usuarios.nombre as profesor')
                ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id')
                ->join('materias', 'materias.id = grupo_materia_profesor.materia_id')
                ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id')
                ->findAll(),
            'inscripciones' => $this->grupoAlumnoModel
                ->select('grupo_alumno.*, grupos.nombre as grupo, usuarios.nombre as alumno')
                ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
                ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
                ->findAll()
        ];

        return view('lms/admin/asignaciones/index', $data);
    }

    // ✅ Asignar profesor a materia-grupo
    public function asignarProfesor()
    {
        $grupoId = $this->request->getPost('grupo_id');
        $materiaId = $this->request->getPost('materia_id');
        $profesorId = $this->request->getPost('profesor_id');

        $horario = implode('-', $this->request->getPost('dias')) . ' ' .
            $this->request->getPost('hora_inicio') . '-' .
            $this->request->getPost('hora_fin');

        // 1️⃣ Crear asignación profesor-grupo-materia
        $this->grupoMateriaProfesorModel->insert([
            'grupo_id' => $grupoId,
            'materia_id' => $materiaId,
            'profesor_id' => $profesorId,
            'ciclo' => $this->request->getPost('ciclo'),
            'aula' => $this->request->getPost('aula'),
            'horario' => $horario,
        ]);

        $asignacionId = $this->grupoMateriaProfesorModel->getInsertID();

        // 2️⃣ Vincular automáticamente alumnos ya inscritos en el grupo
        $alumnos = $this->grupoAlumnoModel->where('grupo_id', $grupoId)->findAll();
        foreach ($alumnos as $alumno) {
            $this->db->table('materia_grupo_alumno')->insert([
                'grupo_materia_profesor_id' => $asignacionId,
                'grupo_alumno_id' => $alumno['id'],
                'calificacion_final' => null,
                'asistencia' => 0,
            ]);
        }

        return redirect()->back()->with('msg', 'Profesor asignado correctamente y alumnos vinculados');
    }

    // ✅ Asignar alumno al grupo (y sus materias)
    public function asignarAlumno()
    {
        $grupoId = $this->request->getPost('grupo_id');
        $alumnoId = $this->request->getPost('alumno_id');

        // 1️⃣ Insertar alumno en grupo
        $this->grupoAlumnoModel->insert([
            'grupo_id' => $grupoId,
            'alumno_id' => $alumnoId,
            'fecha_inscripcion' => date('Y-m-d'),
            'estatus' => 'Inscrito'
        ]);

        $grupoAlumnoId = $this->grupoAlumnoModel->getInsertID();

        // 2️⃣ Obtener todas las asignaciones (materias) del grupo
        $asignaciones = $this->grupoMateriaProfesorModel->where('grupo_id', $grupoId)->findAll();

        // 3️⃣ Insertar vínculos materia-grupo-alumno
        foreach ($asignaciones as $asignacion) {
            $this->db->table('materia_grupo_alumno')->insert([
                'grupo_materia_profesor_id' => $asignacion['id'],
                'grupo_alumno_id' => $grupoAlumnoId,
                'calificacion_final' => null,
                'asistencia' => 0,
            ]);
        }

        return redirect()->back()->with('msg', 'Alumno inscrito correctamente en el grupo y materias');
    }

    // 🔹 Eliminar profesor
    public function eliminarProfesor($id)
    {
        $this->grupoMateriaProfesorModel->delete($id);
        return redirect()->back()->with('msg', 'Asignación eliminada correctamente');
    }

    // 🔹 Eliminar alumno
    public function eliminarAlumno($id)
    {
        $this->grupoAlumnoModel->delete($id);
        return redirect()->back()->with('msg', 'Inscripción eliminada correctamente');
    }
}
