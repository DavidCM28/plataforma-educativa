<?php

namespace App\Models;

use CodeIgniter\Model;

class CicloAcademicoModel extends Model
{
    protected $table = 'ciclos_academicos';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'nombre',
        'descripcion',
        'num_parciales',
        'duracion_meses',
        'activo'
    ];

    protected $validationRules = [
        'nombre' => 'required|string|max_length[100]',
        'num_parciales' => 'required|is_natural_no_zero',
        'duracion_meses' => 'required|is_natural_no_zero',
        'activo' => 'in_list[0,1]'
    ];
}
