<?php

namespace App\Models;

use CodeIgniter\Model;

class AsistenciaModel extends Model
{
    protected $table = 'asistencias';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'materia_grupo_alumno_id',
        'fecha',
        'estado',
        'frecuencias',
        'observaciones',
        'created_at',
        'updated_at'
    ];

    /**
     * 🔹 Obtener asistencias de una asignación por fecha (incluye frecuencia)
     */
    public function obtenerPorAsignacionYFecha($asignacionId, $fecha)
    {
        return $this->db->table($this->table . ' a')
            ->select('
                a.id,
                a.materia_grupo_alumno_id,
                a.fecha,
                a.estado,
                a.frecuencias,
                a.observaciones,
                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.matricula
            ')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->join('grupo_alumno ga', 'ga.id = mga.grupo_alumno_id', 'left')
            ->join('usuarios u', 'u.id = ga.alumno_id', 'left')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->where('a.fecha', $fecha)
            ->get()
            ->getResultArray();
    }

    /**
     * 🔹 Verificar si ya existe registro de asistencia para un día (y frecuencia opcional)
     */
    public function existeRegistro($asignacionId, $fecha, $frecuencia = null)
    {
        $builder = $this->db->table('asistencias a')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->where('a.fecha', $fecha);

        if ($frecuencia !== null) {
            $builder->where('a.frecuencias', $frecuencia);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * 🔹 Obtener todas las fechas en las que hubo registros de asistencia
     * para una asignación específica (agrupadas por fecha)
     */
    public function obtenerFechasRegistradas($asignacionId)
    {
        return $this->db->table('asistencias a')
            ->select('DISTINCT(a.fecha) as fecha')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->orderBy('a.fecha', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * 🔹 Obtener frecuencias registradas para una fecha concreta
     * (útil para validar si ya se marcó una hora específica)
     */
    public function obtenerFrecuenciasPorFecha($asignacionId, $fecha)
    {
        return $this->db->table('asistencias a')
            ->select('DISTINCT(a.frecuencias) as frecuencia')
            ->join('materia_grupo_alumno mga', 'mga.id = a.materia_grupo_alumno_id')
            ->where('mga.grupo_materia_profesor_id', $asignacionId)
            ->where('a.fecha', $fecha)
            ->orderBy('a.frecuencias', 'ASC')
            ->get()
            ->getResultArray();
    }
}
