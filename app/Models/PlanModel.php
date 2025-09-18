<?php
namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'planes_estudio';
    protected $primaryKey = 'id';
    protected $allowedFields = ['carrera_id', 'nombre', 'descripcion', 'anio', 'created_at', 'updated_at'];

    public function getMateriasByCarreraSlug($slug)
    {
        return $this->db->table('materias m')
            ->select('m.nombre, m.descripcion, m.creditos, pm.ciclo') // 👈 Agregamos ciclo
            ->join('plan_materias pm', 'pm.materia_id = m.id')
            ->join('planes_estudio pe', 'pe.id = pm.plan_id')
            ->join('carreras c', 'c.id = pe.carrera_id')
            ->where('c.slug', $slug)
            ->orderBy('pm.ciclo ASC') // 👈 Ordenamos por ciclo
            ->get()
            ->getResultArray();
    }
}
