<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoModel extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nombre',
        'periodo',
        'turno',
        'activo',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Genera el nombre automático del grupo.
     */
    public function generarNombre($siglas, $ciclo, $turno)
    {
        $inicialTurno = strtoupper($turno[0]); // M, V o M (mixto)
        $base = strtoupper($siglas . $ciclo . $inicialTurno);

        // Buscar si ya existen grupos similares
        $count = $this->where('nombre LIKE', "$base%")->countAllResults();

        // Si ya hay uno o más grupos con el mismo patrón
        if ($count > 0) {
            $base .= ($count + 1);
        }

        return $base;
    }
}
