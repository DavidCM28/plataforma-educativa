<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatModel extends Model
{
    protected $table = 'chats';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'tipo',
        'creado_por'
    ];

    /**
     * Obtiene todos los chats en los que está un usuario
     */
    public function obtenerChatsDeUsuario($usuarioId)
    {
        return $this->select('chats.*')
            ->join('chat_usuarios', 'chat_usuarios.chat_id = chats.id')
            ->where('chat_usuarios.usuario_id', $usuarioId)
            ->orderBy('chats.creado_en', 'DESC')
            ->findAll();
    }

    /**
     * Verifica si un usuario está en un chat
     */
    public function usuarioEnChat($chatId, $usuarioId)
    {
        return $this->db->table('chat_usuarios')
            ->where([
                'chat_id' => $chatId,
                'usuario_id' => $usuarioId
            ])
            ->countAllResults() > 0;
    }
}
