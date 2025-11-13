<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenRespuestaDetalleModel extends Model
{
    protected $table = 'examen_respuesta_detalle';
    protected $allowedFields = [
        'respuesta_id',
        'pregunta_id',
        'opcion_id',
        'respuesta_texto',
        'puntos_obtenidos',
        'observacion'
    ];
}
