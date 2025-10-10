<?php

namespace App\Models;

use CodeIgniter\Model;

class MateriaModel extends Model
{
    protected $table = 'materias';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'clave',
        'nombre',
        'descripcion',
        'creditos',
        'horas_semana',
        'activo',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    // ✅ Reglas para creación
    protected $validationRules = [
        'clave' => 'required|min_length[2]|max_length[20]|is_unique[materias.clave]',
        'nombre' => 'required|min_length[3]|max_length[150]',
        'creditos' => 'required|integer',
        'horas_semana' => 'required|integer',
    ];

    protected $validationMessages = [
        'clave' => [
            'required' => 'La clave es obligatoria.',
            'is_unique' => 'Ya existe una materia con esa clave.',
        ],
        'nombre' => [
            'required' => 'El nombre es obligatorio.',
        ],
    ];

    protected $skipValidation = false;

    // 🔧 Regla específica para actualizar
    public function getUpdateRules($id)
    {
        return [
            'clave' => "required|min_length[2]|max_length[20]|is_unique[materias.clave,id,{$id}]",
            'nombre' => 'required|min_length[3]|max_length[150]',
            'creditos' => 'required|integer',
            'horas_semana' => 'required|integer',
        ];
    }
}
