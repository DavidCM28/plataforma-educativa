<?php

namespace App\Models;

use CodeIgniter\Model;

class ProyectoModel extends Model
{
    protected $table = 'proyectos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'asignacion_id',
        'profesor_id',
        'titulo',
        'descripcion',
        'fecha_entrega',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function obtenerPorAsignacion(int $asignacionId)
    {
        return $this->where('asignacion_id', $asignacionId)
            ->orderBy('fecha_entrega', 'ASC')
            ->findAll();
    }

    public function obtenerConArchivos(int $proyectoId)
    {
        $proyecto = $this->find($proyectoId);
        if (!$proyecto)
            return null;

        $archivoModel = new \App\Models\ProyectoArchivoModel();
        $proyecto['archivos'] = $archivoModel->where('proyecto_id', $proyectoId)->findAll();
        return $proyecto;
    }
}
