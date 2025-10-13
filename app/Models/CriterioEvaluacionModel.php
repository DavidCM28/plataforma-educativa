<?php

namespace App\Models;

use CodeIgniter\Model;

class CriterioEvaluacionModel extends Model
{
    protected $table = 'criterios_evaluacion';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'descripcion', 'activo', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}
