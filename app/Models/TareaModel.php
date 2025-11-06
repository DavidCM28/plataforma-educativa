<?php

namespace App\Models;

use CodeIgniter\Model;

class TareaModel extends Model
{
    protected $table = 'tareas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'asignacion_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'fecha_entrega',
        'parcial_numero',
        'criterio_id',
        'porcentaje_tarea',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';

    /**
     * 🧩 Obtener todas las tareas de una asignación (grupo-materia-profesor)
     */
    public function obtenerPorAsignacion($asignacionId)
    {
        return $this->select('tareas.*, criterios_evaluacion.nombre AS criterio_nombre')
            ->join('criterios_evaluacion', 'criterios_evaluacion.id = tareas.criterio_id', 'left')
            ->where('asignacion_id', $asignacionId)
            ->orderBy('fecha_entrega', 'ASC')
            ->findAll();
    }


    /**
     * 📦 Obtener una tarea con sus archivos relacionados
     */
    public function obtenerConArchivos(int $tareaId)
    {
        $tarea = $this->find($tareaId);

        if (!$tarea)
            return null;

        $archivoModel = new \App\Models\TareaArchivoModel();
        $tarea['archivos'] = $archivoModel->where('tarea_id', $tareaId)->findAll();

        return $tarea;
    }
}
