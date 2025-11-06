<?php

namespace App\Models;

use CodeIgniter\Model;

class PonderacionCicloModel extends Model
{
    protected $table = 'ponderaciones_ciclo';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ciclo_id', 'parcial_num', 'criterio_id', 'porcentaje'];


    public function obtenerPorCicloYParcial($cicloId, $parcialNum)
    {
        return $this->where('ciclo_id', $cicloId)
            ->where('parcial_num', $parcialNum)
            ->findAll();
    }

}

