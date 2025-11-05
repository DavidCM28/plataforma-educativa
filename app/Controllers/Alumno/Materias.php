<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\AsistenciaModel;

class Materias extends BaseController
{
    /**
     * 📚 Listado general de materias del alumno
     * URL: /alumno/materias
     */
    public function index()
    {
        $usuario = session('usuario') ?? [];
        $alumnoId = $usuario['id'] ?? session('id') ?? null;

        if (!$alumnoId) {
            return redirect()->to('/login');
        }

        $mgaModel = new MateriaGrupoAlumnoModel();
        $materias = $mgaModel->obtenerMateriasPorAlumno($alumnoId);

        return view('lms/alumno/materias/index', [
            'materias' => $materias
        ]);
    }

    /**
     * 🧭 Vista principal de una materia específica (tipo Teams)
     * URL: /alumno/materias/ver/{id}
     */
    public function ver($asignacionId)
    {
        $usuario = session('usuario') ?? [];
        $alumnoId = $usuario['id'] ?? session('id') ?? null;

        if (!$alumnoId) {
            return redirect()->to('/login');
        }

        $grupoMateriaModel = new GrupoMateriaProfesorModel();
        $mgaModel = new MateriaGrupoAlumnoModel();

        // 🔹 Obtener datos de la materia (nombre, grupo, profesor)
        $materia = $grupoMateriaModel
            ->select('
                gmp.id,
                m.nombre AS nombre,
                g.nombre AS grupo,
                u.nombre AS profesor,
                gmp.horario,
                gmp.aula
            ')
            ->from('grupo_materia_profesor gmp')
            ->join('materias m', 'm.id = gmp.materia_id')
            ->join('grupos g', 'g.id = gmp.grupo_id')
            ->join('usuarios u', 'u.id = gmp.profesor_id')
            ->where('gmp.id', $asignacionId)
            ->first();

        if (!$materia) {
            return redirect()->to('/alumno/dashboard')->with('error', 'No tienes acceso a esta materia.');
        }

        // 🔹 Verificar si el alumno pertenece a esta materia
        $existe = $mgaModel
            ->where('grupo_materia_profesor_id', $asignacionId)
            ->join('grupo_alumno ga', 'ga.id = materia_grupo_alumno.grupo_alumno_id')
            ->where('ga.alumno_id', $alumnoId)
            ->first();

        if (!$existe) {
            return redirect()->to('/alumno/dashboard')->with('error', 'No estás inscrito en esta materia.');
        }

        // Cargar vista principal (tipo Teams)
        return view('lms/alumno/materias/index', [
            'materia' => $materia,
            'asignacionId' => $asignacionId
        ]);
    }

    /**
     * 📘 TAREAS del alumno para la materia
     * URL: /alumno/materias/tareas/{id}
     */
    public function tareas($asignacionId)
    {
        // (Por ahora solo interfaz tipo “Cargando tareas...”)
        return view('lms/alumno/materias/tareas', [
            'asignacionId' => $asignacionId
        ]);
    }

    /**
     * 🚀 PROYECTOS del alumno para la materia
     * URL: /alumno/materias/proyectos/{id}
     */
    public function proyectos($asignacionId)
    {
        return view('lms/alumno/materias/proyectos', [
            'asignacionId' => $asignacionId
        ]);
    }

    /**
     * 📕 EXÁMENES del alumno para la materia
     * URL: /alumno/materias/examenes/{id}
     */
    public function examenes($asignacionId)
    {
        return view('lms/alumno/materias/examenes', [
            'asignacionId' => $asignacionId
        ]);
    }

    /**
     * 📅 HISTORIAL DE ASISTENCIAS del alumno
     * URL: /alumno/materias/asistencias/{id}
     */
    public function asistencias($asignacionId)
    {
        $usuario = session('usuario') ?? [];
        $alumnoId = $usuario['id'] ?? session('id') ?? null;

        if (!$alumnoId) {
            return redirect()->to('/login');
        }

        $asistenciaModel = new AsistenciaModel();

        // 🔹 Obtener asistencias del alumno en esa materia
        $asistencias = $asistenciaModel
            ->select('a.fecha, a.estado, a.frecuencias, a.observaciones')
            ->from('asistencias a')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
            ->where('ga.alumno_id', $alumnoId)
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->orderBy('a.fecha', 'DESC')
            ->findAll();

        return view('lms/alumno/materias/asistencias', [
            'asistencias' => $asistencias,
            'asignacionId' => $asignacionId
        ]);
    }

    /**
     * 📊 CALIFICACIONES del alumno
     * URL: /alumno/materias/calificaciones/{id}
     */
    public function calificaciones($asignacionId)
    {
        return view('lms/alumno/materias/calificaciones', [
            'asignacionId' => $asignacionId
        ]);
    }
}
