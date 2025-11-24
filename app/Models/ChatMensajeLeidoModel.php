<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMensajeLeidoModel extends Model
{
    protected $table = 'chat_mensajes_leidos';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'mensaje_id',
        'usuario_id'
    ];

    public function marcarLeido($mensajeId, $usuarioId)
    {
        return $this->insert([
            'mensaje_id' => $mensajeId,
            'usuario_id' => $usuarioId
        ]);
    }

    public function mensajesLeidosPor($chatId, $usuarioId)
    {
        return $this->select('mensaje_id')
            ->join('chat_mensajes', 'chat_mensajes.id = chat_mensajes_leidos.mensaje_id')
            ->where('chat_mensajes.chat_id', $chatId)
            ->where('chat_mensajes_leidos.usuario_id', $usuarioId)
            ->findAll();
    }
}
