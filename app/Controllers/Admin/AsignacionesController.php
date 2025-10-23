<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\MateriaModel;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\CicloAcademicoModel;
use App\Models\PlanMateriaModel;
use App\Models\PlanEstudioModel;
use App\Models\CarreraGrupoModel;

class AsignacionesController extends BaseController
{
    protected $grupoModel, $materiaModel, $usuarioModel, $grupoMateriaProfesorModel,
    $cicloModel, $planModel, $planMateriaModel, $carreraGrupoModel, $db;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->materiaModel = new MateriaModel();
        $this->usuarioModel = new UsuarioModel();
        $this->grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();
        $this->cicloModel = new CicloAcademicoModel();
        $this->planModel = new PlanEstudioModel();
        $this->planMateriaModel = new PlanMateriaModel();
        $this->carreraGrupoModel = new CarreraGrupoModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // 🔹 Profesores
        $grupos = $this->carreraGrupoModel->obtenerGruposCompletos();
        $materias = $this->materiaModel
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();
        $profesores = $this->usuarioModel
            ->where('rol_id', 3) // 3 = profesor
            ->orderBy('nombre', 'ASC')
            ->findAll();
        $ciclos = $this->cicloModel
            ->orderBy('id', 'DESC')
            ->findAll();

        // 🔹 Alumnos
        $alumnos = $this->usuarioModel
            ->select('id, nombre, matricula')
            ->where('rol_id', 4) // 4 = alumno
            ->orderBy('nombre', 'ASC')
            ->findAll();

        // 🔹 Carreras
        $carreras = $this->db->table('carreras')
            ->select('id, nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();

        // 🔹 Relación alumno ↔ carrera
        $vinculos = $this->db->table('alumno_carrera')
            ->select('alumno_carrera.id, usuarios.matricula, usuarios.nombre AS alumno, carreras.nombre AS carrera, alumno_carrera.estatus')
            ->join('usuarios', 'usuarios.id = alumno_carrera.alumno_id')
            ->join('carreras', 'carreras.id = alumno_carrera.carrera_id')
            ->orderBy('usuarios.nombre', 'ASC')
            ->get()
            ->getResultArray();

        // 🔹 Inscripciones (alumnos ↔ grupos)
        $inscripciones = $this->db->table('grupo_alumno')
            ->select('grupo_alumno.*, grupos.nombre as grupo, usuarios.nombre as alumno')
            ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
            ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
            ->get()
            ->getResultArray();

        // 🔹 Filtrar solo grupos del primer ciclo (para el tab de alumnos)
        $gruposPrimerCiclo = array_filter($grupos, function ($g) {
            // Coincide con '1' que no está precedido ni seguido por otro dígito (evita 10, 11, etc.)
            return preg_match('/(?<!\d)1(?!\d)/', $g['grupo']);
        });

        // 🔹 Guardar copia completa (para el tab de promoción)
        $gruposTotales = $grupos;

        // 🔹 Enviar todo a la vista
        return view('lms/admin/asignaciones/index', [
            'grupos' => $grupos,
            'gruposPrimerCiclo' => $gruposPrimerCiclo,
            'gruposTotales' => $gruposTotales,
            'materias' => $materias,
            'profesores' => $profesores,
            'ciclos' => $ciclos,
            'alumnos' => $alumnos,
            'carreras' => $carreras,
            'vinculos' => $vinculos,
            'inscripciones' => $inscripciones,
        ]);
    }


    /* =========================================================
       👨‍🏫 Asignar profesor a materia-grupo
       ========================================================= */
    public function asignarProfesor()
    {
        $post = $this->request->getPost();
        $grupo_id = $post['grupo_id'] ?? null;
        $materia_id = $post['materia_id'] ?? null;
        $profesor_id = $post['profesor_id'] ?? null;
        $aula = $post['aula'] ?? null;
        $ciclo_id = $post['ciclo_id'] ?? null;

        $ciclo_nombre = null;
        if ($ciclo_id) {
            $ciclo = $this->cicloModel->find($ciclo_id);
            $ciclo_nombre = $ciclo['nombre'] ?? null;
        }

        if (!$grupo_id || !$materia_id || !$profesor_id) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Faltan datos (grupo, materia o profesor).']);
        }

        $horarios = json_decode($post['horarios_json'] ?? '[]', true);
        if (!$horarios || !is_array($horarios) || empty($horarios)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Selecciona al menos un bloque de horario.']);
        }

        $materia = $this->materiaModel->find($materia_id);
        if (!$materia) {
            return $this->response->setJSON(['ok' => false, 'msg' => '❌ Materia no encontrada.']);
        }

        $frecuenciasRequeridas = (int) $materia['horas_semana'];
        $bloquesSeleccionados = array_sum(array_map('count', $horarios));

        $asignacionesPrevias = $this->grupoMateriaProfesorModel
            ->where('grupo_id', $grupo_id)
            ->where('materia_id', $materia_id)
            ->findAll();

        $bloquesPrevios = 0;
        foreach ($asignacionesPrevias as $asig) {
            $bloquesPrevios += count(explode(';', $asig['horario']));
        }

        $totalBloques = $bloquesPrevios + $bloquesSeleccionados;
        $restantes = $frecuenciasRequeridas - $totalBloques;

        if ($totalBloques > $frecuenciasRequeridas) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => "⚠️ La materia «{$materia['nombre']}» solo requiere $frecuenciasRequeridas frecuencias por semana."
            ]);
        }

        $bloquesTexto = [];
        // 🔹 Detectar turno del grupo (matutino o vespertino)
        $grupo = $this->grupoModel->find($grupo_id);
        $turno = strtolower($grupo['turno'] ?? 'matutino'); // Asegura minúsculas
        $duracion = $turno === 'vespertino' ? 40 : 50; // 40 min vespertino, 50 min matutino

        $bloquesTexto = [];
        foreach ($horarios as $dia => $horas) {
            sort($horas);
            foreach ($horas as $hInicio) {
                // Usar la duración correcta
                $hFin = $this->calcularFin($hInicio, $duracion);

                $inicioMin = $this->horaToMinutos($hInicio);
                $finMin = $this->horaToMinutos($hFin);

                // Verificar choques
                if ($conf = $this->hayChoqueHorario($grupo_id, [$dia], $inicioMin, $finMin, 'grupo'))
                    return $this->response->setJSON(['ok' => false, 'msg' => "⚠️ Choque con GRUPO: $conf el día $dia"]);
                if ($conf = $this->hayChoqueHorario($profesor_id, [$dia], $inicioMin, $finMin, 'profesor'))
                    return $this->response->setJSON(['ok' => false, 'msg' => "⚠️ Choque con PROFESOR: $conf el día $dia"]);

                $bloquesTexto[] = "{$dia} {$hInicio}-{$hFin}";
            }
        }



        $asignacionExistente = $this->grupoMateriaProfesorModel
            ->where('grupo_id', $grupo_id)
            ->where('materia_id', $materia_id)
            ->where('profesor_id', $profesor_id)
            ->first();

        if ($asignacionExistente) {
            // Concatenar nuevo horario al existente
            $horarioPrevio = $asignacionExistente['horario'] ?? '';
            $nuevoHorario = trim($horarioPrevio ? "$horarioPrevio; " . implode('; ', $bloquesTexto) : implode('; ', $bloquesTexto));

            $this->grupoMateriaProfesorModel->update($asignacionExistente['id'], [
                'horario' => $nuevoHorario,
                'aula' => $aula,
                'ciclo_id' => $ciclo_id,
                'ciclo' => $ciclo_nombre,
            ]);
        } else {
            // Nueva asignación
            $this->grupoMateriaProfesorModel->insert([
                'grupo_id' => $grupo_id,
                'materia_id' => $materia_id,
                'profesor_id' => $profesor_id,
                'horario' => implode('; ', $bloquesTexto),
                'aula' => $aula,
                'ciclo_id' => $ciclo_id,
                'ciclo' => $ciclo_nombre,
            ]);
        }

        $this->sincronizarMateriaGrupoAlumno($grupo_id);
        return $this->response->setJSON(['ok' => true, 'msg' => "✅ Asignación guardada. Restan $restantes frecuencias."]);
    }

    /* =========================================================
       ✏️ Actualizar asignación
       ========================================================= */
    public function actualizarAsignacion($id)
    {
        $asig = $this->grupoMateriaProfesorModel->find($id);
        if (!$asig)
            return $this->response->setJSON(['ok' => false, 'msg' => '❌ Asignación no encontrada.']);

        $profesor = $this->request->getPost('profesor_id');
        $aula = $this->request->getPost('aula');
        $ciclo_id = $this->request->getPost('ciclo_id');

        $ciclo_nombre = null;
        if ($ciclo_id) {
            $ciclo = $this->cicloModel->find($ciclo_id);
            $ciclo_nombre = $ciclo['nombre'] ?? null;
        }

        $horario = $asig['horario'] ?? null;
        if (empty($horario) || $horario === '-') {
            $horario = $asig['horario'];
        }

        $this->grupoMateriaProfesorModel->update($id, [
            'profesor_id' => $profesor,
            'aula' => $aula,
            'ciclo_id' => $ciclo_id,
            'ciclo' => $ciclo_nombre,
            'horario' => $horario,
        ]);

        return $this->response->setJSON(['ok' => true, 'msg' => '✏️ Asignación actualizada correctamente.']);
    }

    /* =========================================================
       📋 Detalle (para edición)
       ========================================================= */
    public function detalle($id)
    {
        $asig = $this->grupoMateriaProfesorModel
            ->select('
            grupo_materia_profesor.*,
            materias.nombre AS materia_nombre,
            usuarios.nombre AS profesor_nombre,
            ciclos_academicos.id AS ciclo_id,
            ciclos_academicos.nombre AS ciclo_nombre
        ')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id', 'left')
            ->join('ciclos_academicos', 'ciclos_academicos.id = grupo_materia_profesor.ciclo_id', 'left')
            ->find($id);

        if (!$asig)
            return $this->response->setJSON(['ok' => false]);

        $bloques = [];
        $horario = trim($asig['horario'] ?? '');
        if ($horario !== '') {
            $partes = array_filter(array_map('trim', explode(';', $horario)));
            foreach ($partes as $bloque) {
                if (preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $bloque, $m)) {
                    $bloques[] = [
                        'dias' => str_split($m[1]),
                        'hora_inicio' => $m[2],
                        'hora_fin' => $m[3]
                    ];
                }
            }
        }

        $asig['bloques'] = $bloques;
        $asig['ciclo_id'] = $asig['ciclo_id'] ?? null;
        $asig['ciclo'] = $asig['ciclo_nombre'] ?? $asig['ciclo'] ?? '';

        return $this->response->setJSON(['ok' => true, 'asignacion' => $asig]);
    }
    public function materiasPorGrupo($grupoId)
    {
        $relacion = $this->carreraGrupoModel
            ->select('carrera_grupo.carrera_id, grupos.periodo')
            ->join('grupos', 'grupos.id = carrera_grupo.grupo_id')
            ->where('carrera_grupo.grupo_id', $grupoId)
            ->first();

        if (!$relacion) {
            return $this->response->setJSON(['ok' => false, 'msg' => '❌ No se encontró la relación del grupo con una carrera.']);
        }

        $carreraId = $relacion['carrera_id'];
        $periodo = $relacion['periodo'];

        $plan = $this->planModel
            ->where('carrera_id', $carreraId)
            ->where('activo', 1)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$plan) {
            return $this->response->setJSON(['ok' => false, 'msg' => '⚠️ No se encontró un plan activo para la carrera seleccionada.']);
        }

        $materias = $this->planMateriaModel
            ->select('materias.id, materias.nombre, plan_materias.cuatrimestre, plan_materias.tipo')
            ->join('materias', 'materias.id = plan_materias.materia_id')
            ->where('plan_materias.plan_id', $plan['id'])
            ->where('plan_materias.cuatrimestre', $periodo)
            ->where('materias.activo', 1)
            ->orderBy('materias.nombre', 'ASC')
            ->findAll();

        if (!$materias) {
            return $this->response->setJSON(['ok' => false, 'msg' => '⚠️ No hay materias registradas para el ciclo ' . $periodo]);
        }

        return $this->response->setJSON(['ok' => true, 'materias' => $materias]);
    }

    /* =========================================================
       🗑️ Frecuencias
       ========================================================= */
    public function eliminarFrecuencia($id)
    {
        $asig = $this->grupoMateriaProfesorModel->find($id);
        if (!$asig) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Asignación no encontrada.']);
        }

        $dia = $this->request->getPost('dia');
        $inicio = $this->request->getPost('inicio');
        $fin = $this->request->getPost('fin');

        if (!$dia || !$inicio || !$fin) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Faltan datos de frecuencia.']);
        }

        $bloques = array_filter(array_map('trim', explode(';', $asig['horario'] ?? '')));
        $nuevosBloques = [];

        foreach ($bloques as $bloque) {
            if (!preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $bloque, $m))
                continue;

            $diasTxt = $m[1];
            $bInicio = $m[2];
            $bFin = $m[3];

            // Convertir a minutos para comparación flexible
            $bInicioMin = $this->horaToMinutos($bInicio);
            $bFinMin = $this->horaToMinutos($bFin);
            $inicioMin = $this->horaToMinutos($inicio);
            $finMin = $this->horaToMinutos($fin);

            // 💡 Se considera coincidencia si el inicio es igual y el fin es muy cercano (±5 min)
            $finCoincide = abs($bFinMin - $finMin) <= 5;

            if ($bInicio === $inicio && $finCoincide && str_contains($diasTxt, $dia)) {
                // Eliminar solo el día seleccionado
                $diasRestantes = str_replace($dia, '', $diasTxt);
                if ($diasRestantes !== '') {
                    $nuevosBloques[] = $diasRestantes . ' ' . $bInicio . '-' . $bFin;
                }
            } else {
                $nuevosBloques[] = $bloque;
            }
        }

        if (empty($nuevosBloques)) {
            $this->grupoMateriaProfesorModel->delete($id);

            $grupoId = $asig['grupo_id'];
            $this->grupoMateriaProfesorModel->delete($id);
            $this->sincronizarMateriaGrupoAlumno($grupoId);
            return $this->response->setJSON(['ok' => true, 'msg' => '🗑️ Frecuencia eliminada y asignación vacía eliminada.']);

        }

        $this->grupoMateriaProfesorModel->update($id, ['horario' => implode('; ', $nuevosBloques)]);
        return $this->response->setJSON(['ok' => true, 'msg' => '✅ Frecuencia eliminada correctamente.']);
    }


    public function actualizarFrecuencia($id)
    {
        $asig = $this->grupoMateriaProfesorModel->find($id);
        if (!$asig) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Asignación no encontrada.']);
        }

        $oldDia = $this->request->getPost('old_dia');
        $oldInicio = $this->request->getPost('old_inicio');
        $oldFin = $this->request->getPost('old_fin');
        $newDias = $this->request->getPost('new_dias');
        $newInicio = $this->request->getPost('new_inicio');
        $newFin = $this->request->getPost('new_fin');

        if (!$oldDia || !$oldInicio || !$oldFin || !$newDias || !$newInicio || !$newFin) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Faltan datos para actualizar la frecuencia.']);
        }

        $inicioMin = $this->horaToMinutos($newInicio);
        $finMin = $this->horaToMinutos($newFin);
        $diasArray = str_split($newDias);

        $grupoId = $asig['grupo_id'];
        $profesorId = $asig['profesor_id'];

        if ($conf = $this->hayChoqueHorario($grupoId, $diasArray, $inicioMin, $finMin, 'grupo'))
            return $this->response->setJSON(['ok' => false, 'msg' => "⚠️ Choque con el GRUPO: $conf"]);
        if ($conf = $this->hayChoqueHorario($profesorId, $diasArray, $inicioMin, $finMin, 'profesor'))
            return $this->response->setJSON(['ok' => false, 'msg' => "⚠️ Choque con el PROFESOR: $conf"]);

        $bloques = array_filter(array_map('trim', explode(';', $asig['horario'] ?? '')));
        $nuevosBloques = [];
        $reemplazado = false;

        foreach ($bloques as $bloque) {
            if (!preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $bloque, $m))
                continue;
            $diasTxt = $m[1];
            $bInicio = $m[2];
            $bFin = $m[3];

            if ($bInicio === $oldInicio && $bFin === $oldFin && str_contains($diasTxt, $oldDia) && !$reemplazado) {
                $diasRestantes = str_replace($oldDia, '', $diasTxt);
                if ($diasRestantes !== '')
                    $nuevosBloques[] = $diasRestantes . ' ' . $bInicio . '-' . $bFin;
                $nuevosBloques[] = $newDias . ' ' . $newInicio . '-' . $newFin;
                $reemplazado = true;
            } else
                $nuevosBloques[] = $bloque;
        }

        if (!$reemplazado) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No se encontró la frecuencia a actualizar.']);
        }

        $this->grupoMateriaProfesorModel->update($id, ['horario' => implode('; ', $nuevosBloques)]);
        return $this->response->setJSON(['ok' => true, 'msg' => 'Frecuencia actualizada correctamente.']);
    }

    /* =========================================================
       ⚙️ Auxiliares
       ========================================================= */
    private function calcularFin($inicio, $duracion = 50)
    {
        $t = \DateTime::createFromFormat('H:i', $inicio);
        $t->modify("+{$duracion} minutes");
        return $t->format('H:i');
    }


    private function horaToMinutos($hora)
    {
        [$h, $m] = explode(':', $hora);
        return (int) $h * 60 + (int) $m;
    }

    private function hayChoqueHorario($id, $dias, $inicioMin, $finMin, $modo = 'grupo')
    {
        $builder = $this->grupoMateriaProfesorModel
            ->select('materias.nombre AS materia, grupo_materia_profesor.horario')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id');
        $builder->where($modo === 'grupo' ? 'grupo_id' : 'profesor_id', $id);
        $asignaciones = $builder->findAll();

        foreach ($asignaciones as $a) {
            $bloques = array_filter(array_map('trim', explode(';', $a['horario'] ?? '')));
            foreach ($bloques as $bloque) {
                // Coincide con formato: "LMXJV 07:30-08:20"
                if (preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $bloque, $m)) {
                    $diasTxt = str_split($m[1]);

                    // 💡 Verificar si hay coincidencia de día
                    if (array_intersect($diasTxt, $dias)) {
                        $iniExist = $this->horaToMinutos($m[2]);
                        $finExist = $this->horaToMinutos($m[3]);

                        /**
                         * 💡 Detección robusta de choques por rango:
                         * Se considera choque si los rangos de tiempo se superponen, sin importar duración exacta.
                         * Ejemplo:
                         *   Nuevo: 17:20-18:00
                         *   Existente: 17:00-17:40  → choque (se solapan)
                         *   Existente: 18:00-18:40  → NO choque (fin = inicio)
                         */
                        $seSolapan = !($finMin <= $iniExist || $inicioMin >= $finExist);

                        if ($seSolapan) {
                            return $a['materia']; // ⚠️ Choque detectado
                        }
                    }
                }
            }
        }

        return false; // ✅ Sin choques
    }




    /* =========================================================
   🗓️ Horario visual del grupo
   ========================================================= */
    public function horarioGrupo($grupoId)
    {
        $asignaciones = $this->grupoMateriaProfesorModel
            ->select('
            grupo_materia_profesor.id,
            grupo_materia_profesor.grupo_id,
            grupo_materia_profesor.materia_id,
            grupo_materia_profesor.profesor_id,
            grupo_materia_profesor.horario,
            grupo_materia_profesor.aula,
            materias.nombre AS materia,
            usuarios.nombre AS profesor
        ')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id', 'left')
            ->where('grupo_materia_profesor.grupo_id', $grupoId)
            ->findAll();

        $result = [];
        foreach ($asignaciones as $a) {
            $bloques = array_filter(array_map('trim', explode(';', $a['horario'] ?? '')));
            foreach ($bloques as $b) {
                if (preg_match('/^([LMXJV]+)\s+(\d{2}:\d{2})-(\d{2}:\d{2})$/', $b, $m)) {
                    $result[] = [
                        'id' => $a['id'],
                        'grupo_id' => $a['grupo_id'],
                        'materia_id' => $a['materia_id'],
                        'profesor_id' => $a['profesor_id'],
                        'materia' => $a['materia'],
                        'profesor' => $a['profesor'],
                        'dias' => str_split($m[1]),
                        'inicio_str' => $m[2],
                        'fin_str' => $m[3],
                        'aula' => $a['aula'] ?? '',
                    ];
                }
            }
        }

        return $this->response->setJSON(['ok' => true, 'asignaciones' => $result]);
    }

    /* =========================================================
   ⏱️ Frecuencias restantes por materia en grupo
   ========================================================= */
    public function frecuenciasRestantes($grupoId, $materiaId)
    {
        $materia = $this->materiaModel->find($materiaId);
        if (!$materia) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '❌ Materia no encontrada.'
            ]);
        }

        $totales = (int) $materia['horas_semana'];

        $asignaciones = $this->grupoMateriaProfesorModel
            ->where('grupo_id', $grupoId)
            ->where('materia_id', $materiaId)
            ->findAll();

        $usadas = 0;
        foreach ($asignaciones as $a) {
            $bloques = array_filter(array_map('trim', explode(';', $a['horario'] ?? '')));
            $usadas += count($bloques);
        }

        $restantes = max(0, $totales - $usadas);

        return $this->response->setJSON([
            'ok' => true,
            'totales' => $totales,
            'usadas' => $usadas,
            'restantes' => $restantes
        ]);
    }

    /* =========================================================
       🗑️ Eliminar asignación completa (profesor ↔ materia-grupo)
       ========================================================= */
    public function eliminarProfesor($id)
    {
        $asig = $this->grupoMateriaProfesorModel->find($id);
        if (!$asig) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '❌ Asignación no encontrada.'
            ]);
        }

        $grupoId = $asig['grupo_id'];
        $this->grupoMateriaProfesorModel->delete($id);
        $this->sincronizarMateriaGrupoAlumno($grupoId);
        return $this->response->setJSON([
            'ok' => true,
            'msg' => '🗑️ Asignación eliminada correctamente.'
        ]);

    }

    /**
     * Sincroniza las relaciones materia-grupo-alumno.
     * - Crea vínculos nuevos para alumnos que aún no tengan el registro.
     * - Elimina los vínculos de materias eliminadas.
     */
    private function sincronizarMateriaGrupoAlumno($grupoId)
    {
        // 1️⃣ Obtener las asignaciones activas del grupo (materias-profesor)
        $asignaciones = $this->grupoMateriaProfesorModel
            ->select('id')
            ->where('grupo_id', $grupoId)
            ->findAll();

        $idsAsignaciones = array_column($asignaciones, 'id');

        // 2️⃣ Obtener alumnos inscritos en el grupo
        $alumnosGrupo = $this->db->table('grupo_alumno')
            ->select('id')
            ->where('grupo_id', $grupoId)
            ->get()
            ->getResultArray();

        $idsGrupoAlumno = array_column($alumnosGrupo, 'id');

        // 3️⃣ Si no hay alumnos o asignaciones, no hay nada que vincular
        if (empty($idsAsignaciones) || empty($idsGrupoAlumno)) {
            return;
        }

        $tabla = $this->db->table('materia_grupo_alumno');

        // 4️⃣ Eliminar registros huérfanos (de asignaciones eliminadas)
        $tabla->whereNotIn('grupo_materia_profesor_id', $idsAsignaciones)->delete();

        // 5️⃣ Insertar vínculos que falten (sin duplicar)
        foreach ($idsAsignaciones as $idAsig) {
            foreach ($idsGrupoAlumno as $idGrupoAlumno) {
                $existe = $this->db->table('materia_grupo_alumno')
                    ->where('grupo_materia_profesor_id', $idAsig)
                    ->where('grupo_alumno_id', $idGrupoAlumno)
                    ->countAllResults();

                if ($existe == 0) {
                    $tabla->insert([
                        'grupo_materia_profesor_id' => $idAsig,
                        'grupo_alumno_id' => $idGrupoAlumno,
                        'calificacion_final' => null,
                        'asistencia' => 0,
                    ]);
                }
            }
        }
    }


}
