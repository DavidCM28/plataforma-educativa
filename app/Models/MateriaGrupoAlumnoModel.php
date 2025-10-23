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



}
