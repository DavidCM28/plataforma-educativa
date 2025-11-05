<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\GrupoAlumnoModel;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\GrupoMateriaProfesorModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $usuario = session('usuario') ?? [];
        $alumnoId = $usuario['id'] ?? session('id') ?? null;

        if (!$alumnoId) {
            return redirect()->to('/login');
        }

        $grupoAlumnoModel = new GrupoAlumnoModel();
        $materiaGrupoAlumnoModel = new MateriaGrupoAlumnoModel();
        $grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();

        // =========================================================
        // 🔹 1. Grupos en los que está inscrito el alumno
        // =========================================================
        $grupos = $grupoAlumnoModel
            ->select('grupo_alumno.*, grupos.nombre AS grupo, grupos.id AS grupo_id')
            ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
            ->where('grupo_alumno.alumno_id', $alumnoId)
            ->where('grupo_alumno.estatus', 'Inscrito')
            ->findAll();

        if (empty($grupos)) {
            return view('lms/dashboards/alumno_dashboard', [
                'materias' => [],
                'horario' => [],
                'promedio' => '-',
                'asistencia' => '-'
            ]);
        }

        // =========================================================
        // 🔹 2. Obtener materias asignadas a esos grupos (únicas)
        // =========================================================
        $gruposIds = array_column($grupos, 'grupo_id');

        $materias = $grupoMateriaProfesorModel
            ->select('
        gmp.id AS asignacion_id,  
        gmp.materia_id,
        m.nombre AS materia,
        g.nombre AS grupo,
        u.nombre AS profesor,
        GROUP_CONCAT(DISTINCT gmp.horario SEPARATOR "; ") AS horarios,
        GROUP_CONCAT(DISTINCT gmp.aula SEPARATOR ", ") AS aulas
    ')
            ->from('grupo_materia_profesor gmp')
            ->join('materias m', 'm.id = gmp.materia_id')
            ->join('grupos g', 'g.id = gmp.grupo_id')
            ->join('usuarios u', 'u.id = gmp.profesor_id')
            ->whereIn('gmp.grupo_id', $gruposIds)
            ->groupBy('gmp.id, gmp.materia_id, g.nombre, u.nombre')
            ->findAll();



        // =========================================================
        // 🔹 3. Construir horario semanal
        // =========================================================
        $horario = ['L' => [], 'M' => [], 'X' => [], 'J' => [], 'V' => []];

        foreach ($materias as &$m) {
            $texto = trim($m['horarios'] ?? '');
            if (!empty($texto)) {
                $bloques = array_filter(array_map('trim', preg_split('/[;,]+/', $texto)));

                foreach ($bloques as $bloque) {
                    if (preg_match('/^([A-ZÁÉÍÓÚÑ])\s+([0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2})$/u', $bloque, $m2)) {
                        $dia = strtoupper(trim($m2[1]));
                        $hora = trim($m2[2]);
                        $horario[$dia][] = [
                            'materia_id' => $m['materia_id'],
                            'materia' => $m['materia'],
                            'grupo' => $m['grupo'],
                            'hora' => $hora,
                            'aula' => $m['aulas'] ?? null
                        ];
                    }
                }
            }
        }
        unset($m);


        // =========================================================
        // 🔹 4. Calcular promedio y asistencia (si existen registros)
        // =========================================================
        $materiasAlumno = $materiaGrupoAlumnoModel
            ->whereIn('grupo_alumno_id', array_column($grupos, 'id'))
            ->findAll();

        $promedio = '-';
        $asistencia = '-';

        if (!empty($materiasAlumno)) {
            $totalCalif = 0;
            $totalAsist = 0;
            $n = 0;

            foreach ($materiasAlumno as $ma) {
                if (is_numeric($ma['calificacion_final'])) {
                    $totalCalif += $ma['calificacion_final'];
                }
                if (is_numeric($ma['asistencia'])) {
                    $totalAsist += $ma['asistencia'];
                }
                $n++;
            }

            if ($n > 0) {
                $promedio = number_format($totalCalif / $n, 1);
                $asistencia = number_format($totalAsist / $n, 1);
            }
        }

        // =========================================================
        // 🔹 5. Enviar datos a la vista
        // =========================================================
        return view('lms/dashboards/alumno_dashboard', [
            'materias' => $materias,
            'horario' => $horario,
            'promedio' => $promedio,
            'asistencia' => $asistencia
        ]);
    }
}
