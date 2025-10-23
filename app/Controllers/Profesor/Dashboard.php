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

        foreach ($asignaciones as &$asignacion) {
            $texto = trim($asignacion['horario'] ?? '');

            if (!empty($texto)) {
                // 🔹 Separar cada bloque por punto y coma (;)
                $bloques = array_filter(array_map('trim', preg_split('/[;,]+/', $texto)));

                $mapa = []; // ['L' => ['07:30-08:20'], 'X' => ['10:00-10:50', '10:50-11:40']]

                foreach ($bloques as $bloque) {
                    // Coincide con formato tipo "L 07:30-08:20"
                    if (preg_match('/^([A-ZÁÉÍÓÚÑ])\s+([0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2})$/u', $bloque, $m)) {
                        $dia = strtoupper(trim($m[1]));
                        $hora = trim($m[2]);
                        $mapa[$dia][] = $hora;
                    }
                }

                // 🔹 Crear versiones legibles
                if (!empty($mapa)) {
                    $diasArray = [];
                    $horasArray = [];
                    foreach ($mapa as $dia => $horas) {
                        $diasArray[] = $dia;
                        $horasArray[] = implode(', ', $horas);
                    }

                    $asignacion['dias'] = implode(' / ', $diasArray);
                    $asignacion['hora'] = implode(' / ', $horasArray);
                    $asignacion['horario_detalle'] = $mapa;
                } else {
                    $asignacion['dias'] = '-';
                    $asignacion['hora'] = '-';
                    $asignacion['horario_detalle'] = [];
                }
            } else {
                $asignacion['dias'] = '-';
                $asignacion['hora'] = '-';
                $asignacion['horario_detalle'] = [];
            }
        }
        unset($asignacion);



        return view('lms/dashboards/profesor_dashboard', [
            'totales' => $datos,
            'asignaciones' => $asignaciones
        ]);
    }
}
