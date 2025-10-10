<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanMateriaModel extends Model
{
    protected $table = 'plan_materias';
    protected $primaryKey = 'id';
    protected $allowedFields = ['plan_id', 'materia_id', 'cuatrimestre', 'tipo'];

    protected $validationRules = [
        'plan_id' => 'required|integer',
        'materia_id' => 'required|integer',
        'cuatrimestre' => 'required|integer|greater_than_equal_to[1]',
        'tipo' => 'in_list[Tronco Común,Especialidad,Optativa]'
    ];

    // 🔗 Obtener materias por plan
    public function getMateriasPorPlan($planId)
    {
        return $this->select('plan_materias.*, materias.clave, materias.nombre')
            ->join('materias', 'materias.id = plan_materias.materia_id')
            ->where('plan_id', $planId)
            ->orderBy('cuatrimestre', 'ASC')
            ->findAll();
    }
}
