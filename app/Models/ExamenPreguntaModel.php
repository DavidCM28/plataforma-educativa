<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenPreguntaModel extends Model
{
    protected $table = 'examen_preguntas';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'examen_id',
        'tipo',
        'pregunta',
        'imagen',
        'puntos',
        'es_extra',
        'orden'
    ];
    protected $useTimestamps = true;

    public function obtenerPorExamen($examenId)
    {
        return $this->where('examen_id', $examenId)
            ->orderBy('orden', 'ASC')
            ->findAll();
    }
}
