<?php

namespace App\Models;

use CodeIgniter\Model;

class TareaArchivoModel extends Model
{
    protected $table = 'tareas_archivos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tarea_id',
        'archivo',
        'tipo',
        'created_at'
    ];
    protected $useTimestamps = false; // ✅ no existe updated_at en la tabla
    protected $returnType = 'array';

    /**
     * 📎 Obtener todos los archivos relacionados a una tarea
     */
    public function obtenerPorTarea(int $tareaId)
    {
        return $this->where('tarea_id', $tareaId)
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * 🗑️ Eliminar archivos por tarea (al eliminar la tarea principal)
     */
    public function eliminarPorTarea(int $tareaId)
    {
        return $this->where('tarea_id', $tareaId)->delete();
    }
}
