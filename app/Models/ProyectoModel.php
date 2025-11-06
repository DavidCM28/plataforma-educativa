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
        'parcial_numero',
        'criterio_id',
        'porcentaje_proyecto',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';

    /**
     * 📘 Obtener todos los proyectos de una asignación (grupo-materia-profesor)
     */
    public function obtenerPorAsignacion(int $asignacionId)
    {
        return $this->select('proyectos.*, criterios_evaluacion.nombre AS criterio_nombre')
            ->join('criterios_evaluacion', 'criterios_evaluacion.id = proyectos.criterio_id', 'left')
            ->where('asignacion_id', $asignacionId)
            ->orderBy('fecha_entrega', 'ASC')
            ->findAll();
    }

    /**
     * 📦 Obtener un proyecto con sus archivos relacionados
     */
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
