<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\GrupoMateriaProfesorModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $usuario = session('usuario') ?? [];
        $profesorId = $usuario['id'] ?? session('id') ?? null;

        $model = new GrupoMateriaProfesorModel();
        $datos = $model->obtenerTotalesPorProfesor($profesorId);
        $asignaciones = $model->obtenerAsignacionesPorProfesor($profesorId);

        // 🔹 Separar días y hora del campo horario (robusto)
        // 🔹 Separar días y hora del campo horario (versión definitiva)
        foreach ($asignaciones as &$asignacion) {
            $horario = trim(preg_replace('/\s+/', ' ', $asignacion['horario'] ?? ''));

            if (!empty($horario)) {
                // Coincide con formatos tipo "L-X-V 08:20-09:00" o "M-J 10:00-12:00"
                if (preg_match('/^([A-ZÁÉÍÓÚÑ\-]+)\s+([0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2})$/u', $horario, $matches)) {
                    $asignacion['dias'] = $matches[1] ?? '-';
                    $asignacion['hora'] = $matches[2] ?? '-';
                } else {
                    // Respaldo si el formato no cumple con el patrón
                    $partes = explode(' ', $horario, 2);
                    $asignacion['dias'] = trim($partes[0] ?? '-');
                    $asignacion['hora'] = trim($partes[1] ?? '-');
                }
            } else {
                $asignacion['dias'] = '-';
                $asignacion['hora'] = '-';
            }
        }
        unset($asignacion);


        return view('lms/dashboards/profesor_dashboard', [
            'totales' => $datos,
            'asignaciones' => $asignaciones
        ]);
    }
}
