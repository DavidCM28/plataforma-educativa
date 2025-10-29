<?php

namespace App\Models;

use CodeIgniter\Model;

class ProyectoArchivoModel extends Model
{
    protected $table = 'proyectos_archivos';
    protected $primaryKey = 'id';
    protected $allowedFields = ['proyecto_id', 'archivo', 'tipo', 'created_at'];
    protected $useTimestamps = false;
    protected $returnType = 'array';

    public function obtenerPorProyecto(int $id)
    {
        return $this->where('proyecto_id', $id)->findAll();
    }

    public function eliminarPorProyecto(int $id)
    {
        return $this->where('proyecto_id', $id)->delete();
    }
}
