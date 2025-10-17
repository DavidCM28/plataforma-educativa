<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\UsuarioModel;
use App\Models\TareaModel;
use App\Models\CalificacionModel;

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

        $usuariosModel = new UsuarioModel();
        $tareasModel = new TareaModel();
        $calificacionesModel = new CalificacionModel();

        $alumnos = $usuariosModel->obtenerPorGrupo($asignacionId);
        $tareas = $tareasModel->obtenerPorGrupo($asignacionId);
        $calificaciones = $calificacionesModel->obtenerPorGrupo($asignacionId);

        return view('lms/profesor/grupos/index', [
            'grupo' => $grupo,
            'alumnos' => $alumnos,
            'tareas' => $tareas,
            'calificaciones' => $calificaciones,
        ]);
    }
}