<?php

namespace App\Models;

use CodeIgniter\Model;

class AlumnoCarreraModel extends Model
{
    protected $table = 'alumno_carrera';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'alumno_id',
        'carrera_id',
        'fecha_registro',
        'estatus',
    ];
    protected $useTimestamps = false;
}
