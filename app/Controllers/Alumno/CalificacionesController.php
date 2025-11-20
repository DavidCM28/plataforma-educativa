<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\CalificacionParcialModel;
use App\Models\GrupoAlumnoModel;

class CalificacionesController extends BaseController
{
    protected $db;
    protected $mgaModel;
    protected $asignacionModel;
    protected $parcialModel;
    protected $grupoAlumnoModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->mgaModel = new MateriaGrupoAlumnoModel();
        $this->asignacionModel = new GrupoMateriaProfesorModel();
        $this->parcialModel = new CalificacionParcialModel();
        $this->grupoAlumnoModel = new GrupoAlumnoModel();
    }

    /* =====================================================
       📌 Vista principal
    ====================================================== */
    public function index()
    {
        $alumnoId = session('id');

        if (!$alumnoId) {
            return redirect()->to('/login');
        }

        // 1️⃣ Materias activas del ciclo actual
        $materias = $this->mgaModel->obtenerMateriasPorAlumno($alumnoId);

        // 2️⃣ Historial (kárdex)
        $kardex = $this->obtenerKardex($alumnoId);

        // 3️⃣ Promedio global del kardex
        $promedioGeneral = $this->calcularPromedioKardex($kardex);

        return view('lms/alumno/extras/calificaciones', [
            'materiasActuales' => $materias,
            'kardex' => $kardex,
            'promedioGeneral' => $promedioGeneral
        ]);
    }

    /* =====================================================
       📌 Obtener parciales por materia (AJAX)
       /alumno/calificaciones/parciales/{asignacion_id}
    ====================================================== */
    public function obtenerParciales($asignacionId)
    {
        $alumnoId = session('id');

        // ---------------------------------------------
        // 1) Obtener ciclo_id desde la asignación
        // ---------------------------------------------
        $asignacion = $this->db->table('grupo_materia_profesor')
            ->select('ciclo_id')
            ->where('id', $asignacionId)
            ->get()
            ->getRowArray();

        if (!$asignacion) {
            return $this->response->setJSON([]);
        }

        $cicloId = $asignacion['ciclo_id'];

        // ---------------------------------------------
        // 2) Obtener parciales reales del ciclo
        // ---------------------------------------------
        $parciales = $this->db->table('ciclos_parciales')
            ->where('ciclo_id', $cicloId)
            ->orderBy('numero_parcial', 'ASC')
            ->get()
            ->getResultArray();

        // ---------------------------------------------
        // 3) Obtener ID de materia_grupo_alumno (mga.id)
        // ---------------------------------------------
        $mga = $this->db->table('materia_grupo_alumno mga')
            ->select('mga.id')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
            ->where('ga.alumno_id', $alumnoId)
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->get()
            ->getRowArray();

        if (!$mga) {
            return $this->response->setJSON([]);
        }

        $mgaId = $mga['id'];

        // ---------------------------------------------
        // 4) Obtener calificaciones existentes del alumno
        // ---------------------------------------------
        $calificaciones = $this->parcialModel
            ->where('materia_grupo_alumno_id', $mgaId)
            ->findAll();

        // Indexar por ciclo_parcial_id
        $mapCalif = [];
        foreach ($calificaciones as $c) {
            $mapCalif[$c['ciclo_parcial_id']] = $c['calificacion'];
        }

        // ---------------------------------------------
        // 5) Construir respuesta
        // ---------------------------------------------
        $resultado = [];
        $suma = 0;
        $cuenta = 0;

        foreach ($parciales as $p) {
            $idParcial = $p['id'];
            $numero = $p['numero_parcial'];
            $calif = $mapCalif[$idParcial] ?? null;

            $resultado[] = [
                'id' => $idParcial,
                'numero' => $numero,
                'calificacion' => $calif
            ];

            if ($calif !== null) {
                $suma += $calif;
                $cuenta++;
            }
        }

        // ---------------------------------------------
        // 6) Final dinámico: promedio de parciales existentes
        // ---------------------------------------------
        $final = $cuenta > 0 ? round($suma / $cuenta, 1) : null;

        return $this->response->setJSON([
            'parciales' => $resultado,
            'final' => $final
        ]);
    }


    /* =====================================================
       📌 Obtener kardex completo
    ====================================================== */
    private function obtenerKardex($alumnoId)
    {
        return $this->db->table('materia_grupo_alumno mga')
            ->select('
                m.nombre AS materia,
                gmp.ciclo AS ciclo,
                u.nombre AS profesor,
                mga.calificacion_final AS final
            ')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
            ->join('grupo_materia_profesor gmp', 'gmp.id = mga.grupo_materia_profesor_id')
            ->join('materias m', 'm.id = gmp.materia_id')
            ->join('usuarios u', 'u.id = gmp.profesor_id')
            ->where('ga.alumno_id', $alumnoId)
            ->orderBy('gmp.ciclo', 'ASC')
            ->get()
            ->getResultArray();
    }

    /* =====================================================
       📌 Calcular promedio general del kardex
    ====================================================== */
    private function calcularPromedioKardex($kardex)
    {
        if (empty($kardex))
            return '-';

        $sum = 0;
        $n = 0;

        foreach ($kardex as $k) {
            if (is_numeric($k['final'])) {
                $sum += $k['final'];
                $n++;
            }
        }

        return $n > 0 ? number_format($sum / $n, 1) : '-';
    }

    public function tablaCiclo()
    {
        $alumnoId = session('id');

        if (!$alumnoId) {
            return $this->response->setJSON(['error' => 'No autorizado']);
        }

        // 1) Obtener ciclo actual desde cualquier asignación activa
        $asignacion = $this->db->table('grupo_materia_profesor gmp')
            ->select('gmp.ciclo_id')
            ->join('grupo_alumno ga', 'ga.grupo_id = gmp.grupo_id')
            ->where('ga.alumno_id', $alumnoId)
            ->limit(1)
            ->get()
            ->getRowArray();

        if (!$asignacion) {
            return $this->response->setJSON([
                'parciales' => [],
                'materias' => []
            ]);
        }

        $cicloId = $asignacion['ciclo_id'];

        // 2) Obtener parciales reales del ciclo
        $parciales = $this->db->table('ciclos_parciales')
            ->where('ciclo_id', $cicloId)
            ->orderBy('numero_parcial', 'ASC')
            ->get()
            ->getResultArray();

        // Formato que el JS espera
        $listaParciales = array_map(fn($p) => [
            'id' => $p['id'],
            'numero' => $p['numero_parcial']
        ], $parciales);

        // 3) Obtener todas las materias del alumno
        $materias = $this->db->table('materia_grupo_alumno mga')
            ->select('
            mga.id AS mga_id,
            gmp.id AS asignacion_id,
            m.nombre AS materia,
            gmp.materia_id
        ')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
            ->join('grupo_materia_profesor gmp', 'gmp.id = mga.grupo_materia_profesor_id')
            ->join('materias m', 'm.id = gmp.materia_id')
            ->where('ga.alumno_id', $alumnoId)
            ->get()
            ->getResultArray();

        // 4) Calificaciones por cada materia/parcial
        $resultadoMaterias = [];

        foreach ($materias as $m) {

            // Calificaciones parciales del alumno en esta materia
            $califs = $this->parcialModel
                ->where('materia_grupo_alumno_id', $m['mga_id'])
                ->findAll();

            // Indexar por ciclo_parcial_id
            $map = [];
            foreach ($califs as $c) {
                $map[$c['ciclo_parcial_id']] = $c['calificacion'];
            }

            // Generar matriz parcial → calificación
            $porParcial = [];
            $suma = 0;
            $cuenta = 0;

            foreach ($parciales as $p) {
                $cal = $map[$p['id']] ?? null;
                $porParcial[$p['numero_parcial']] = $cal;

                if ($cal !== null) {
                    $suma += $cal;
                    $cuenta++;
                }
            }

            // Promedio final dinámico
            $final = $cuenta > 0 ? round($suma / $cuenta, 1) : null;

            $resultadoMaterias[] = [
                'asignacion_id' => $m['asignacion_id'],
                'materia' => $m['materia'],
                'parciales' => $porParcial,
                'final' => $final
            ];
        }

        return $this->response->setJSON([
            'parciales' => $listaParciales,
            'materias' => $resultadoMaterias
        ]);
    }
    public function criterios($asignacionId, $parcialNum)
    {
        $alumnoId = session('id');

        // --------------------------------------------
        // 1) Obtener ciclo_parcial_id y ciclo_id
        // --------------------------------------------
        $cicloParcial = $this->db->table('ciclos_parciales cp')
            ->join('grupo_materia_profesor gmp', 'gmp.ciclo_id = cp.ciclo_id')
            ->where('gmp.id', $asignacionId)
            ->where('cp.numero_parcial', $parcialNum)
            ->select('cp.id AS parcial_id, cp.ciclo_id')
            ->get()
            ->getRowArray();

        if (!$cicloParcial) {
            return $this->response->setJSON(['items' => []]);
        }

        $parcialId = $cicloParcial['parcial_id'];
        $cicloId = $cicloParcial['ciclo_id'];

        // --------------------------------------------
        // 2) Obtener MGA.id del alumno
        // --------------------------------------------
        $mga = $this->db->table('materia_grupo_alumno mga')
            ->select('mga.id')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id')
            ->where('ga.alumno_id', $alumnoId)
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->get()
            ->getRowArray();

        if (!$mga) {
            return $this->response->setJSON(['items' => []]);
        }

        $mgaId = $mga['id'];

        // --------------------------------------------
        // 3) Ponderación global (asistencia/participación/examen)
        // --------------------------------------------
        $ponderaciones = $this->db->table('ponderaciones_ciclo')
            ->where('ciclo_id', $cicloId)
            ->where('parcial_num', $parcialNum)
            ->get()
            ->getResultArray();

        $mapPond = [];
        foreach ($ponderaciones as $p) {
            $mapPond[$p['criterio_id']] = floatval($p['porcentaje']);
        }

        // --------------------------------------------
        // 4) Registros reales del parcial
        // --------------------------------------------
        $registros = $this->db->table('calificaciones_parcial cp')
            ->join('criterios_evaluacion ce', 'ce.id = cp.criterio_id')
            ->select('cp.calificacion, cp.item_id, cp.item_tipo, cp.criterio_id, ce.nombre AS criterio')
            ->where('cp.materia_grupo_alumno_id', $mgaId)
            ->where('cp.ciclo_parcial_id', $parcialId)
            ->orderBy('criterio', 'ASC')
            ->get()
            ->getResultArray();

        $conteo = [];
        $items = [];

        foreach ($registros as $r) {

            $crit = $r['criterio'];

            // Numeración elegante (solo si hay varias)
            if (!isset($conteo[$crit]))
                $conteo[$crit] = 0;
            $conteo[$crit]++;

            $nombreFinal = $crit;
            if ($conteo[$crit] > 1) {
                $nombreFinal .= " " . $conteo[$crit];
            }

            // --------------------------------------------
            // 5) Detectar porcentaje real segun item_tipo
            // --------------------------------------------
            $porcentaje = 0;

            switch ($r['item_tipo']) {

                // ===========================
                // 📘 TAREAS  →  alias "t_1"
                // ===========================
                case 'tarea':
                    if (preg_match('/^t_(\d+)$/', $r['item_id'], $m)) {
                        $num = intval($m[1]); // tarea N

                        $tareas = $this->db->table('tareas')
                            ->where('asignacion_id', $asignacionId)
                            ->where('parcial_numero', $parcialNum)
                            ->orderBy('id', 'ASC')
                            ->get()->getResultArray();

                        $task = $tareas[$num - 1] ?? null;
                        $porcentaje = floatval($task['porcentaje_tarea'] ?? 0);
                    }
                    break;

                // ===========================
                // 📗 PROYECTOS  →  alias "p_1"
                // ===========================
                case 'proyecto':
                    if (preg_match('/^p_(\d+)$/', $r['item_id'], $m)) {
                        $num = intval($m[1]);

                        $proyectos = $this->db->table('proyectos')
                            ->where('asignacion_id', $asignacionId)
                            ->where('parcial_numero', $parcialNum)
                            ->orderBy('id', 'ASC')
                            ->get()->getResultArray();

                        $proj = $proyectos[$num - 1] ?? null;
                        $porcentaje = floatval($proj['porcentaje_proyecto'] ?? 0);
                    }
                    break;

                // ===========================
                // 📝 EXAMEN (sin desglose)
                // ===========================
                case 'examen':
                    $porcentaje = $mapPond[$r['criterio_id']] ?? 0;
                    break;

                // ===========================
                // 🟫 Asistencia / Participación
                // ===========================
                default:
                    $porcentaje = $mapPond[$r['criterio_id']] ?? 0;
            }

            // --------------------------------------------
            // 6) Porcentaje obtenido
            // --------------------------------------------
            $porcentaje_obtenido = 0;
            if ($porcentaje > 0 && is_numeric($r['calificacion'])) {
                $porcentaje_obtenido = round(($r['calificacion'] / 10) * $porcentaje, 1);
            }

            $items[] = [
                'criterio' => $nombreFinal,
                'porcentaje' => $porcentaje,
                'calificacion' => $r['calificacion'],
                'porcentaje_obtenido' => $porcentaje_obtenido
            ];
        }

        // --------------------------------------------
        // 7) Calificación del parcial
        // --------------------------------------------
        $final = array_sum(array_column($items, 'porcentaje_obtenido'));

        // --------------------------------------------
        // 8) Nombre de la materia
        // --------------------------------------------
        $materia = $this->db->table('grupo_materia_profesor gmp')
            ->join('materias m', 'm.id = gmp.materia_id')
            ->where('gmp.id', $asignacionId)
            ->select('m.nombre AS materia')
            ->get()->getRowArray()['materia'] ?? '';

        return $this->response->setJSON([
            'materia' => $materia,
            'final' => round($final, 1),
            'items' => $items
        ]);
    }

}
