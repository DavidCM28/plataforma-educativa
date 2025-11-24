<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatUsuarioModel extends Model
{
    protected $table = 'chat_usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'chat_id',
        'usuario_id'
    ];

    public function usuariosDeChat($chatId)
    {
        return $this->where('chat_id', $chatId)->findAll();
    }

    public function agregarUsuario($chatId, $usuarioId)
    {
        return $this->insert([
            'chat_id' => $chatId,
            'usuario_id' => $usuarioId
        ]);
    }

    public function obtenerOtroParticipante($chatId, $usuarioActual)
    {
        return $this->db->table('chat_usuarios cu')
            ->select('cu.usuario_id, u.nombre, u.apellido_paterno, u.apellido_materno, u.foto')
            ->join('usuarios u', 'u.id = cu.usuario_id')
            ->where('cu.chat_id', $chatId)
            ->where('cu.usuario_id !=', $usuarioActual)
            ->get()
            ->getRowArray();
    }


    public function obtenerUsuarioCompleto($usuarioId)
    {
        return $this->db->table('usuarios')
            ->select('id, nombre, apellido_paterno, apellido_materno, foto')
            ->where('id', $usuarioId)
            ->get()
            ->getRowArray();
    }

    public function obtenerIdsDeUsuarios($chatId)
    {
        return $this->db->table('chat_usuarios')
            ->select('usuario_id')
            ->where('chat_id', $chatId)
            ->get()
            ->getResultArray();
    }

}
