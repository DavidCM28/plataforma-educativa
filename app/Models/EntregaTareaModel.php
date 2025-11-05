<?php

namespace App\Models;

use CodeIgniter\Model;

class EntregaTareaModel extends Model
{
    protected $table = 'tareas_entregas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tarea_id',
        'alumno_id',
        'archivo',
        'fecha_entrega',
        'calificacion',
        'retroalimentacion'
    ];

    protected $returnType = 'array';
    public $timestamps = false;

    // 🔹 Verificar estado de entrega de un alumno
    public function obtenerEstadoAlumno($tareaId, $alumnoId)
    {
        $entrega = $this->where('tarea_id', $tareaId)
            ->where('alumno_id', $alumnoId)
            ->first();

        if (!$entrega)
            return 'pendiente';

        $tareaModel = new \App\Models\TareaModel();
        $tarea = $tareaModel->find($tareaId);

        if (!empty($tarea['fecha_entrega']) && strtotime($entrega['fecha_entrega']) > strtotime($tarea['fecha_entrega'])) {
            return 'tarde';
        }

        return 'entregada';
    }
}
