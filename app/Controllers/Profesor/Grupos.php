<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\UsuarioModel;          // ✅ Reemplaza AlumnoModel por UsuarioModel
use App\Models\TareaModel;
use App\Models\CalificacionModel;

class Grupos extends BaseController
{
    public function ver($asignacionId)
    {
        $usuario = session('usuario') ?? [];
        $profesorId = $usuario['id'] ?? session('id') ?? null;

        $grupoModel = new GrupoMateriaProfesorModel();
        $grupo = $grupoModel->obtenerGrupoPorIdYProfesor($asignacionId, $profesorId);

        if (!$grupo) {
            return redirect()->to('/profesor/dashboard')->with('error', 'No tienes acceso a este grupo.');
        }

        // ✅ Modelos actualizados
        $usuariosModel = new UsuarioModel();
        $tareasModel = new TareaModel();
        $calificacionesModel = new CalificacionModel();

        // ✅ Usamos el método obtenerPorGrupo() que agregaremos a UsuarioModel
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
