<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoMateriaProfesorModel extends Model
{
    protected $table = 'grupo_materia_profesor';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    protected $allowedFields = [
        'grupo_id',
        'materia_id',
        'profesor_id',
        'ciclo_id',
        'ciclo',
        'aula',
        'horario',
    ];

    protected $validationRules = [
        'grupo_id' => 'required|is_natural_no_zero',
        'materia_id' => 'required|is_natural_no_zero',
        'profesor_id' => 'required|is_natural_no_zero',
        'ciclo' => 'permit_empty|string|max_length[50]',
        'aula' => 'permit_empty|string|max_length[50]',
        'horario' => 'permit_empty|string|max_length[100]',
    ];

    // 🍬 Listar todas las asignaciones con joins
    public function obtenerAsignaciones()
    {
        return $this->select('
                grupo_materia_profesor.*,
                grupos.nombre AS grupo,
                materias.nombre AS materia,
                usuarios.nombre AS profesor
            ')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id')
            ->findAll();
    }

    // ✅ Asignaciones de un profesor específico
    public function obtenerAsignacionesPorProfesor($profesorId)
    {
        return $this->select('
            grupo_materia_profesor.*,
            grupos.id AS id_grupo,                   
            grupos.nombre AS grupo,
            materias.nombre AS materia,
            materias.clave AS clave_materia,
            ciclos_academicos.nombre AS ciclo
        ')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id', 'left')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('ciclos_academicos', 'ciclos_academicos.id = grupo_materia_profesor.ciclo_id', 'left')
            ->where('grupo_materia_profesor.profesor_id', $profesorId)
            ->findAll();
    }


    // ✅ Totales rápidos para dashboard
    public function obtenerTotalesPorProfesor($profesorId)
    {
        $asignaciones = $this->obtenerAsignacionesPorProfesor($profesorId);

        $materias = [];
        $grupos = [];

        foreach ($asignaciones as &$asignacion) {
            $horarioTexto = trim($asignacion['horario'] ?? '');

            if (!empty($horarioTexto)) {
                // Dividir por punto y coma (;) o coma, quitar espacios extras
                $bloques = array_filter(array_map('trim', preg_split('/[;,]+/', $horarioTexto)));

                $mapaDias = []; // ejemplo: ['L' => ['07:30-08:20'], 'V' => ['07:30-08:20'], 'X' => ['10:00-10:50','10:50-11:40']]

                foreach ($bloques as $bloque) {
                    // Coincide con formatos tipo "X 10:00-10:50"
                    if (preg_match('/^([A-ZÁÉÍÓÚÑ\-]+)\s+([0-9]{1,2}:[0-9]{2}-[0-9]{1,2}:[0-9]{2})$/u', $bloque, $m)) {
                        $dia = trim($m[1]);
                        $hora = trim($m[2]);
                        $mapaDias[$dia][] = $hora;
                    }
                }

                // Convertir a formato legible
                $diasMostrar = [];
                $horasMostrar = [];

                foreach ($mapaDias as $dia => $horas) {
                    $diasMostrar[] = $dia;
                    $horasMostrar[] = implode(', ', $horas);
                }

                $asignacion['dias'] = implode(' / ', $diasMostrar);
                $asignacion['hora'] = implode(' / ', $horasMostrar);
                $asignacion['horario_detalle'] = $mapaDias; // útil para el horario semanal
            } else {
                $asignacion['dias'] = '-';
                $asignacion['hora'] = '-';
                $asignacion['horario_detalle'] = [];
            }
        }
        unset($asignacion);



        return [
            'total_materias' => count($materias),
            'total_grupos' => count($grupos),
            'tareas_pendientes' => rand(3, 15) // ⚠️ Simulado por ahora
        ];
    }

    // ✅ Obtener un grupo específico que pertenezca al profesor
    public function obtenerGrupoPorIdYProfesor($asignacionId, $profesorId)
    {
        return $this->select('
        grupo_materia_profesor.*,
        grupos.id AS id_grupo,
        grupos.nombre AS grupo,
        grupos.periodo,
        grupos.turno,
        materias.id AS id_materia,
        materias.nombre AS materia,
        materias.clave AS clave_materia,
        ciclos_academicos.nombre AS ciclo
    ')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id', 'left')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('ciclos_academicos', 'ciclos_academicos.id = grupo_materia_profesor.ciclo_id', 'left')
            ->where('grupo_materia_profesor.profesor_id', $profesorId)
            ->where('grupo_materia_profesor.id', $asignacionId)
            ->first();
    }



}
