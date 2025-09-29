<?php
namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'email',
        'password',
        'foto',
        'rol_id',
        'matricula',
        'num_empleado',
        'activo',
        'verificado',
        'ultimo_login',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
}
