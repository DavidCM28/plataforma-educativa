<?php

namespace App\Models;

use CodeIgniter\Model;

class CalificacionParcialModel extends Model
{
    protected $table = 'calificaciones_parcial';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $allowedFields = [
        'materia_grupo_alumno_id',
        'ciclo_parcial_id',
        'criterio_id',
        'item_id',
        'item_tipo',
        'calificacion',
        'porcentaje_item',
        'observaciones',
    ];

    /**
     * 🟦 Obtener todas las calificaciones de un alumno en un parcial
     */
    public function obtenerPorAlumnoYParcial($mgaId, $cicloParcialId)
    {
        return $this->where('materia_grupo_alumno_id', $mgaId)
            ->where('ciclo_parcial_id', $cicloParcialId)
            ->findAll();
    }

    /**
     * 🟨 Obtener calificaciones por criterio (examen/tareas/proyecto/etc)
     */
    public function obtenerPorCriterio($mgaId, $cicloParcialId, $criterioId)
    {
        return $this->where([
            'materia_grupo_alumno_id' => $mgaId,
            'ciclo_parcial_id' => $cicloParcialId,
            'criterio_id' => $criterioId
        ])
            ->findAll();
    }

    /**
     * 🟩 Insertar o actualizar (UPSERT) calificación por ítem
     */
    public function guardarItem($data)
    {
        // Buscar si ya existe el registro
        $exist = $this->where('materia_grupo_alumno_id', $data['materia_grupo_alumno_id'])
            ->where('ciclo_parcial_id', $data['ciclo_parcial_id'])
            ->where('criterio_id', $data['criterio_id'])
            ->where('item_id', $data['item_id'])
            ->where('item_tipo', $data['item_tipo'])
            ->first();

        if ($exist) {
            // UPDATE (evita duplicados)
            return $this->update($exist['id'], $data);
        }

        // INSERT (nuevo item)
        return $this->insert($data);
    }


    /**
     * 🟧 Borrar calificaciones de un parcial (opcional)
     */
    public function eliminarParcialAlumno($mgaId, $cicloParcialId)
    {
        return $this->where('materia_grupo_alumno_id', $mgaId)
            ->where('ciclo_parcial_id', $cicloParcialId)
            ->delete();
    }
}
