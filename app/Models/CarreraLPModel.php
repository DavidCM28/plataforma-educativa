<?php

namespace App\Models;

use CodeIgniter\Model;

class CarreraLPModel extends Model
{
    protected $table = 'carreras_publicas';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'nombre',
        'slug',
        'nivel',
        'descripcion',
        'modalidad',
        'duracion',
        'perfil_ingreso',
        'perfil_egreso',
        'campo_laboral',
        'created_at',
        'updated_at'
    ];
}
