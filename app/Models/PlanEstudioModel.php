<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanEstudioModel extends Model
{
    protected $table = 'planes_estudio';
    protected $primaryKey = 'id';
    protected $useTimestamps = true;
    protected $allowedFields = ['carrera_id', 'nombre', 'fecha_vigencia', 'activo'];

    protected $validationRules = [
        'carrera_id' => 'required|integer',
        'nombre' => 'required|min_length[3]|max_length[150]',
        'fecha_vigencia' => 'permit_empty|valid_date[Y-m-d]',
    ];

    protected $validationMessages = [
        'nombre' => [
            'required' => 'El nombre del plan de estudios es obligatorio.',
        ],
        'carrera_id' => [
            'required' => 'Debe seleccionar una carrera asociada.',
        ],
    ];

    // ✅ Relación simple (útil para joins)
    public function withCarrera()
    {
        return $this->select('planes_estudio.*, carreras.nombre AS carrera')
            ->join('carreras', 'carreras.id = planes_estudio.carrera_id');
    }
}
