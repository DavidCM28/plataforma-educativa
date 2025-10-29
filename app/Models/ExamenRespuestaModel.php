<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenRespuestaModel extends Model
{
    protected $table = 'examen_respuestas';
    protected $allowedFields = ['examen_id', 'alumno_id', 'intento', 'calificacion', 'calificado', 'fecha_inicio', 'fecha_fin'];
    protected $useTimestamps = true;

    public function obtenerConDetalle($respuestaId)
    {
        $res = $this->find($respuestaId);
        if (!$res)
            return null;

        $det = model(ExamenRespuestaDetalleModel::class)
            ->where('respuesta_id', $respuestaId)->findAll();

        $res['detalle'] = $det;
        return $res;
    }
}
