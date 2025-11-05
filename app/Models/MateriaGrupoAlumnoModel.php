<?php

namespace App\Models;

use CodeIgniter\Model;

class MateriaGrupoAlumnoModel extends Model
{
    protected $table = 'materia_grupo_alumno';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'grupo_materia_profesor_id',
        'grupo_alumno_id',
        'calificacion_final',
        'asistencia'
    ];

    /**
     * 🔹 Obtener alumnos vinculados a una asignación (materia-grupo-profesor)
     */
    public function obtenerAlumnosPorAsignacion($asignacionId)
    {
        return $this->db->table('materia_grupo_alumno mga')
            ->select('
                mga.id AS mga_id,
                u.id AS alumno_id,
                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.matricula,
                u.foto,
                c.nombre AS carrera
            ')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id', 'left')
            ->join('usuarios u', 'u.id = ga.alumno_id', 'left')
            ->join('alumno_carrera ac', 'ac.alumno_id = u.id', 'left')
            ->join('carreras c', 'c.id = ac.carrera_id', 'left')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->orderBy('u.apellido_paterno', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 📚 Obtener todas las materias inscritas del alumno
     */
    public function obtenerMateriasPorAlumno($alumnoId)
    {
        return $this->db->table('materia_grupo_alumno mga')
            ->select('
                gmp.id AS asignacion_id,       -- ID de la asignación (clave principal para ver materia)
                m.nombre AS materia,           -- Nombre de la materia
                g.nombre AS grupo,             -- Grupo
                u.nombre AS profesor,          -- Profesor asignado
                gmp.horario,                   -- Horario
                gmp.aula,                      -- Aula
                gmp.color                      -- Color personalizado si existe
            ')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id', 'left')
            ->join('grupo_materia_profesor gmp', 'gmp.id = mga.grupo_materia_profesor_id', 'left')
            ->join('materias m', 'm.id = gmp.materia_id', 'left')
            ->join('grupos g', 'g.id = gmp.grupo_id', 'left')
            ->join('usuarios u', 'u.id = gmp.profesor_id', 'left')
            ->where('ga.alumno_id', $alumnoId)
            ->orderBy('m.nombre', 'ASC')
            ->get()
            ->getResultArray();
    }
}
