<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ChatModel;
use App\Models\ChatUsuarioModel;
use App\Models\ChatMensajeModel;

class ChatVistaController extends BaseController
{
    protected $chatModel;
    protected $chatUsuarioModel;
    protected $mensajeModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->chatUsuarioModel = new ChatUsuarioModel();
        $this->mensajeModel = new ChatMensajeModel();
    }

    // ============================
    //   LISTA DE TODOS LOS CHATS
    // ============================
    public function index()
    {
        $usuarioId = session('id');

        $chats = $this->chatModel->obtenerChatsDeUsuario($usuarioId);

        $lista = [];

        foreach ($chats as $chat) {
            $ultimo = $this->mensajeModel->obtenerUltimoMensaje($chat['id']);
            $otro = $this->chatUsuarioModel->obtenerOtroParticipante($chat['id'], $usuarioId);

            $lista[] = [
                'chat_id' => $chat['id'],
                'con' => $otro['usuario_id'] ?? null,
                'ultimo' => $ultimo['mensaje'] ?? '',
                'hora' => $ultimo['enviado_en'] ?? ''
            ];
        }

        return view('lms/chat/index', [
            'chats' => $lista
        ]);
    }

    // ============================
    //     ABRIR CHAT ESPECÍFICO
    // ============================
    public function chat($chatId)
    {
        $usuarioId = session('id');

        // Validar acceso
        if (!$this->chatModel->usuarioEnChat($chatId, $usuarioId)) {
            return redirect()->to('mensajes')->with('error', 'No tienes acceso a este chat');
        }

        $mensajes = $this->mensajeModel->obtenerMensajesDeChat($chatId);

        return view('lms/chat/chat', [
            'chatId' => $chatId,
            'mensajes' => $mensajes
        ]);
    }

    public function vistaCompleta($chatId = null)
    {
        $usuarioId = session('id');

        // LISTA IZQUIERDA
        $chats = $this->chatModel->obtenerChatsDeUsuario($usuarioId);
        $lista = [];

        foreach ($chats as $chat) {

            $ultimo = $this->mensajeModel->obtenerUltimoMensaje($chat['id']);
            $otro = $this->chatUsuarioModel->obtenerOtroParticipante($chat['id'], $usuarioId);

            if (!$otro) {
                // Chat corrupto o incompleto → lo saltamos
                continue;
            }

            $datosUsuario = $this->chatUsuarioModel->obtenerUsuarioCompleto($otro['usuario_id']);

            $lista[] = [
                'chat_id' => $chat['id'],
                'usuario' => $datosUsuario,
                'ultimo' => $ultimo['mensaje'] ?? '',
                'hora' => $ultimo['enviado_en'] ?? ''
            ];
        }


        // PANEL DERECHO: CHAT ABIERTO
        $mensajes = null;
        $otroUsuario = null;

        if ($chatId) {

            $chatExiste = $this->chatModel->find($chatId);
            if (!$chatExiste) {
                return redirect()->to('api/chat/mensajes')
                    ->with('error', 'Este chat ya no existe.');
            }

            if ($this->chatModel->usuarioEnChat($chatId, $usuarioId)) {

                // 🔵 MARCAR COMO LEÍDOS
                $api = new \App\Controllers\Api\ChatController();
                $api->marcarLeidos($chatId);

                $socketData = [
                    'chat_id' => $chatId,
                    'lector_id' => $usuarioId
                ];

                $socket = service('socket'); // si tienes helper ENVÍALO DESDE JS CON FETCH



                // Luego sí obtener mensajes
                $mensajes = $this->mensajeModel->obtenerMensajesDeChat($chatId);

                $otro = $this->chatUsuarioModel->obtenerOtroParticipante($chatId, $usuarioId);
                if ($otro) {
                    $otroUsuario = $this->chatUsuarioModel->obtenerUsuarioCompleto($otro['usuario_id']);
                }
            }
        }

        return view('lms/chat/vista_completa', [
            'chats' => $lista,
            'chatId' => $chatId,
            'mensajes' => $mensajes,
            'otroUsuario' => $otroUsuario
        ]);
    }




}
