<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenPreguntaModel extends Model
{
    protected $table = 'examen_preguntas';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['examen_id', 'tipo', 'pregunta', 'imagen', 'puntos', 'orden'];
    protected $useTimestamps = true;
}
