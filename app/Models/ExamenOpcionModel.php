<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenOpcionModel extends Model
{
    protected $table = 'examen_opciones';
    protected $allowedFields = ['pregunta_id', 'texto', 'es_correcta', 'orden'];
}
