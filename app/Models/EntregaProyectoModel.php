<?php

namespace App\Models;

use CodeIgniter\Model;

class EntregaProyectoModel extends Model
{
    protected $table = 'proyectos_entregas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'proyecto_id',
        'alumno_id',
        'archivo',
        'fecha_entrega',
        'calificacion',
        'retroalimentacion',
        'created_at',
        'updated_at'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;

    public function obtenerEstadoAlumno($proyectoId, $alumnoId)
    {
        $entrega = $this->where('proyecto_id', $proyectoId)
            ->where('alumno_id', $alumnoId)
            ->first();

        if (!$entrega)
            return 'pendiente';

        $proyectoModel = new \App\Models\ProyectoModel();
        $proyecto = $proyectoModel->find($proyectoId);

        if (
            !empty($proyecto['fecha_entrega']) &&
            strtotime($entrega['fecha_entrega']) > strtotime($proyecto['fecha_entrega'])
        ) {
            return 'tarde';
        }

        return 'entregada';
    }
}
