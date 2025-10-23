<?php

namespace App\Models;

use CodeIgniter\Model;

class PublicacionModel extends Model
{
    protected $table = 'publicaciones_grupo';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'grupo_materia_profesor_id',
        'usuario_id',
        'tipo',
        'contenido',
        'fecha_publicacion'
    ];
    protected $useTimestamps = false;

    /**
     * 📥 Obtiene todas las publicaciones de un grupo (con usuario y archivos)
     */
    public function obtenerPorGrupo($asignacionId)
    {
        return $this->select('publicaciones_grupo.*, usuarios.nombre, usuarios.apellido_paterno, usuarios.apellido_materno, usuarios.foto')
            ->join('usuarios', 'usuarios.id = publicaciones_grupo.usuario_id', 'left')
            ->where('grupo_materia_profesor_id', $asignacionId)
            ->orderBy('fecha_publicacion', 'DESC')
            ->findAll();
    }

    /**
     * 📎 Devuelve los archivos vinculados a una publicación
     */
    public function obtenerArchivos($publicacionId)
    {
        return $this->db->table('publicaciones_archivos')
            ->where('publicacion_id', $publicacionId)
            ->get()
            ->getResultArray();
    }

    /**
     * 💬 Devuelve los comentarios de una publicación (si después quieres usarlos)
     */
    public function obtenerComentarios($publicacionId)
    {
        return $this->db->table('publicaciones_comentarios pc')
            ->select('pc.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.foto')
            ->join('usuarios u', 'u.id = pc.usuario_id', 'left')
            ->where('pc.publicacion_id', $publicacionId)
            ->orderBy('pc.fecha', 'ASC')
            ->get()
            ->getResultArray();
    }
}
