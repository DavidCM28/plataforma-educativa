<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamenModel extends Model
{
    protected $table = 'examenes';
    protected $useSoftDeletes = true;
    protected $allowedFields = [
        'asignacion_id',
        'profesor_id',
        'parcial_num',
        'criterio_id',
        'titulo',
        'descripcion',
        'instrucciones',
        'tiempo_minutos',
        'puntos_totales',
        'intentos_maximos',
        'fecha_publicacion',
        'fecha_cierre',
        'estado'
    ];
    protected $useTimestamps = true;

    public function obtenerPorAsignacion($asignacionId)
    {
        return $this->where('asignacion_id', $asignacionId)
            ->orderBy('id', 'DESC')->findAll();
    }

    public function obtenerConPreguntas($id)
    {
        $examen = $this->find($id);
        if (!$examen)
            return null;

        $preguntas = model(ExamenPreguntaModel::class)
            ->where('examen_id', $id)
            ->orderBy('orden', 'ASC')
            ->findAll();

        $opModel = model(ExamenOpcionModel::class);
        foreach ($preguntas as &$p) {
            if ($p['tipo'] === 'opcion') {
                $p['opciones'] = $opModel->where('pregunta_id', $p['id'])
                    ->orderBy('orden', 'ASC')->findAll();
            } else {
                $p['opciones'] = [];
            }
        }

        $examen['preguntas'] = $preguntas;
        return $examen;
    }

    public function obtenerPorAsignacionConPorcentaje($asignacionId)
    {
        return $this
            ->select('examenes.*, ponderaciones_ciclo.porcentaje')
            ->join('grupo_materia_profesor gmp', 'gmp.id = examenes.asignacion_id')
            ->join(
                'ponderaciones_ciclo',
                'ponderaciones_ciclo.ciclo_id = gmp.ciclo_id
             AND ponderaciones_ciclo.parcial_num = examenes.parcial_num
             AND ponderaciones_ciclo.criterio_id = examenes.criterio_id',
                'left'
            )
            ->where('examenes.asignacion_id', $asignacionId)
            ->orderBy('examenes.id', 'DESC')
            ->findAll();
    }



}
