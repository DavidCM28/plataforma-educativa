<?php

namespace App\Models;

use CodeIgniter\Model;

class TareaModel extends Model
{
    protected $table = 'tareas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'titulo',
        'descripcion',
        'fecha_entrega',
        'archivo_adjunto',
        'profesor_id',
        'grupo_materia_profesor_id', // 🔹 Nueva relación directa con la asignación
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';

    /**
     * ✅ Obtener tareas por asignación (materia-grupo-profesor)
     */
    public function obtenerPorGrupo($asignacionId)
    {
        return $this->select('tareas.*, materias.nombre AS materia, grupos.nombre AS grupo')
            ->join('grupo_materia_profesor', 'grupo_materia_profesor.id = tareas.grupo_materia_profesor_id', 'left')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id', 'left')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id', 'left')
            ->where('tareas.grupo_materia_profesor_id', $asignacionId)
            ->orderBy('tareas.fecha_entrega', 'DESC')
            ->findAll();
    }

    public function obtenerPorProfesor($profesorId)
    {
        return $this->where('profesor_id', $profesorId)->findAll();
    }
}
