<?php

namespace App\Models;

use CodeIgniter\Model;

class CarreraGrupoModel extends Model
{
    protected $table = 'carrera_grupo';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';

    protected $allowedFields = [
        'carrera_id',
        'grupo_id',
        'tutor_id'
    ];

    public function obtenerGruposCompletos()
    {
        return $this->select([
            'grupos.id AS id',                 // ID real del grupo
            'grupos.nombre AS grupo',          // Nombre del grupo
            'grupos.turno',
            'grupos.periodo',
            'grupos.activo',
            'carreras.id AS carrera_id',
            'carreras.nombre AS carrera',
            'carreras.siglas',
            'usuarios.nombre AS tutor'
        ])
            ->join('grupos', 'grupos.id = carrera_grupo.grupo_id')
            ->join('carreras', 'carreras.id = carrera_grupo.carrera_id')
            ->join('usuarios', 'usuarios.id = carrera_grupo.tutor_id', 'left')
            ->orderBy('carreras.siglas', 'ASC')
            ->findAll();
    }



}
