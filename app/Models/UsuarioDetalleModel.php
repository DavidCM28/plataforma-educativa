<?php

namespace App\Models;

use CodeIgniter\Model;

class UsuarioDetalleModel extends Model
{
    protected $table = 'usuarios_detalles';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'usuario_id',
        // Datos personales
        'sexo',
        'fecha_nacimiento',
        'estado_civil',
        'curp',
        'rfc',
        'pais_origen',

        // Datos médicos
        'peso',
        'estatura',
        'tipo_sangre',
        'antecedente_diabetico',
        'antecedente_hipertenso',
        'antecedente_cardiaco',

        // Domicilio
        'estado',
        'municipio',
        'colonia',
        'calle',
        'numero_exterior',
        'numero_interior',

        // Comunicación
        'telefono',
        'correo_alternativo',
        'telefono_trabajo',

        // Formación académica
        'grado_academico',
        'descripcion_grado',
        'cedula_profesional',

        // Auditoría
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true; // ✅ Para que maneje created_at y updated_at automáticamente
    protected $returnType = 'array';

    /**
     * Obtiene los detalles completos de un usuario (join con la tabla usuarios)
     */
    public function obtenerConUsuario($usuarioId)
    {
        return $this->select('usuarios.*, usuarios_detalles.*')
            ->join('usuarios', 'usuarios.id = usuarios_detalles.usuario_id', 'left')
            ->where('usuarios.id', $usuarioId)
            ->first();
    }

    /**
     * Verifica si un usuario ya tiene detalles registrados
     */
    public function existeDetalle($usuarioId)
    {
        return $this->where('usuario_id', $usuarioId)->first() !== null;
    }

    public function obtenerCompleto($id)
    {
        return $this->select('usuarios.*, usuarios_detalles.*')
            ->join('usuarios_detalles', 'usuarios_detalles.usuario_id = usuarios.id', 'left')
            ->where('usuarios.id', $id)
            ->first();
    }

}
