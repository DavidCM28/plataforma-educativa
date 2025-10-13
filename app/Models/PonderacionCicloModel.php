<?php

namespace App\Models;

use CodeIgniter\Model;

class PonderacionCicloModel extends Model
{
    protected $table = 'ponderaciones_ciclo';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ciclo_id', 'parcial_num', 'criterio_id', 'porcentaje'];
}
