<?php

namespace App\Models;

use CodeIgniter\Model;

class CicloParcialModel extends Model
{
    protected $table = 'ciclos_parciales';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Activar timestamps si tu tabla usa created_at y updated_at
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Campos permitidos
    protected $allowedFields = [
        'ciclo_id',
        'numero_parcial',
        'fecha_inicio',
        'fecha_fin',
    ];

    // Validación opcional pero recomendable
    protected $validationRules = [
        'ciclo_id' => 'required|integer',
        'numero_parcial' => 'required|integer',
        'fecha_inicio' => 'required|valid_date',
        'fecha_fin' => 'required|valid_date',
    ];
}
