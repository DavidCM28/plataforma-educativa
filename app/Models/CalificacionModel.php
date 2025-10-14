<?php

namespace App\Models;

use CodeIgniter\Model;

class CalificacionModel extends Model
{
    protected $table = 'calificaciones';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'materia_grupo_alumno_id',
        'parcial',
        'calificacion',
        'observaciones',
        'fecha_registro'
    ];

    protected $useTimestamps = false;
    protected $returnType = 'array';

    /**
     * ✅ Obtener calificaciones por asignación (materia-grupo-profesor)
     */
    public function obtenerPorGrupo($asignacionId)
    {
        return $this->select('
                calificaciones.*,
                usuarios.nombre,
                usuarios.apellido_paterno,
                usuarios.apellido_materno,
                usuarios.matricula,
                grupos.nombre AS grupo,
                materias.nombre AS materia
            ')
            ->join('materia_grupo_alumno', 'materia_grupo_alumno.id = calificaciones.materia_grupo_alumno_id')
            ->join('grupo_alumno', 'grupo_alumno.id = materia_grupo_alumno.grupo_alumno_id')
            ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
            ->join('grupo_materia_profesor', 'grupo_materia_profesor.id = materia_grupo_alumno.grupo_materia_profesor_id')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id', 'left')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->where('grupo_materia_profesor.id', $asignacionId)
            ->orderBy('usuarios.apellido_paterno', 'ASC')
            ->findAll();
    }
}
