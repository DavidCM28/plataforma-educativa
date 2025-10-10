<?php

namespace App\Models;

use CodeIgniter\Model;

class CarreraModel extends Model
{
    protected $table = 'carreras';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nombre', 'siglas', 'duracion', 'activo'];
    protected $useTimestamps = true;
}
