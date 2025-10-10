<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoMateriaProfesorModel extends Model
{
    protected $table = 'grupo_materia_profesor';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false; // la tabla no tiene created_at/updated_at

    protected $allowedFields = [
        'grupo_id',
        'materia_id',
        'profesor_id',
        'ciclo_id',
        'ciclo',
        'aula',
        'horario',
    ];

    protected $validationRules = [
        'grupo_id' => 'required|is_natural_no_zero',
        'materia_id' => 'required|is_natural_no_zero',
        'profesor_id' => 'required|is_natural_no_zero',
        'ciclo' => 'permit_empty|string|max_length[50]',
        'aula' => 'permit_empty|string|max_length[50]',
        'horario' => 'permit_empty|string|max_length[100]',
    ];

    // 🍬 Helper opcional para listar con joins (si prefieres usarlo desde el controlador)
    public function obtenerAsignaciones()
    {
        return $this->select('grupo_materia_profesor.*, grupos.nombre as grupo, materias.nombre as materia, usuarios.nombre as profesor')
            ->join('grupos', 'grupos.id = grupo_materia_profesor.grupo_id')
            ->join('materias', 'materias.id = grupo_materia_profesor.materia_id')
            ->join('usuarios', 'usuarios.id = grupo_materia_profesor.profesor_id')
            ->findAll();
    }
}
