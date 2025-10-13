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

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->materiaModel = new MateriaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();
        $this->grupoAlumnoModel = new GrupoAlumnoModel();
        $this->cicloModel = new CicloAcademicoModel();
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

    // 🔹 Asignar profesor
    public function asignarProfesor()
    {
        $horario = implode('-', $this->request->getPost('dias')) . ' ' .
            $this->request->getPost('hora_inicio') . '-' .
            $this->request->getPost('hora_fin');

        $this->grupoMateriaProfesorModel->insert([
            'grupo_id' => $this->request->getPost('grupo_id'),
            'materia_id' => $this->request->getPost('materia_id'),
            'profesor_id' => $this->request->getPost('profesor_id'),
            'ciclo' => $this->request->getPost('ciclo'),
            'aula' => $this->request->getPost('aula'),
            'horario' => $horario,
        ]);

        return redirect()->back()->with('msg', 'Profesor asignado correctamente');
    }

    // 🔹 Asignar alumno
    public function asignarAlumno()
    {
        $this->grupoAlumnoModel->insert([
            'grupo_id' => $this->request->getPost('grupo_id'),
            'alumno_id' => $this->request->getPost('alumno_id'),
            'fecha_inscripcion' => date('Y-m-d'),
            'estatus' => 'Inscrito'
        ]);

        return redirect()->back()->with('msg', 'Alumno inscrito correctamente');
    }

    // 🔹 Eliminar asignaciones
    public function eliminarProfesor($id)
    {
        $this->grupoMateriaProfesorModel->delete($id);
        return redirect()->back()->with('msg', 'Asignación eliminada correctamente');
    }

    public function eliminarAlumno($id)
    {
        $this->grupoAlumnoModel->delete($id);
        return redirect()->back()->with('msg', 'Inscripción eliminada correctamente');
    }
}
