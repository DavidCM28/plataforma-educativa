<?php

namespace App\Models;

use CodeIgniter\Model;

class BecaModel extends Model
{
    protected $table = 'becas';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nombre',
        'descripcion',
        'porcentaje',
        'requisitos',
        'servicio_becario_horas',
        'created_at',
        'updated_at'
    ];

    // Activamos timestamps automáticos
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validaciones opcionales
    protected $validationRules = [
        'nombre' => 'required|min_length[3]|max_length[100]',
        'porcentaje' => 'required|integer|greater_than[0]|less_than_equal_to[100]',
    ];

    protected $skipValidation = false;
}
