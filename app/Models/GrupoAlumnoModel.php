<?php

namespace App\Models;

use CodeIgniter\Model;

class GrupoAlumnoModel extends Model
{
    protected $table = 'grupo_alumno';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = false; // la tabla no tiene created_at/updated_at

    protected $allowedFields = [
        'grupo_id',
        'alumno_id',
        'fecha_inscripcion',
        'estatus',
    ];

    protected $validationRules = [
        'grupo_id' => 'required|is_natural_no_zero',
        'alumno_id' => 'required|is_natural_no_zero',
        'fecha_inscripcion' => 'permit_empty|valid_date',
        'estatus' => 'in_list[Inscrito,Baja,Egresado]',
    ];

    // 🍬 Helper opcional para listar con joins
    public function obtenerInscripciones()
    {
        return $this->select('grupo_alumno.*, grupos.nombre as grupo, usuarios.nombre as alumno')
            ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
            ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
            ->findAll();
    }
}
