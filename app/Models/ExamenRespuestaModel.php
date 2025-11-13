<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenRespuestaModel extends Model
{
    protected $table = 'examen_respuestas';
    protected $allowedFields = [
        'examen_id',
        'alumno_id',
        'intento',
        'estado',
        'calificacion',
        'calificado',
        'fecha_inicio',
        'fecha_fin'
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function obtenerPorAlumno($examenId, $alumnoId)
    {
        return $this->where('examen_id', $examenId)
            ->where('alumno_id', $alumnoId)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
