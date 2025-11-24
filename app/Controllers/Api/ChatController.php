<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

use App\Models\ChatModel;
use App\Models\ChatUsuarioModel;
use App\Models\ChatMensajeModel;
use CodeIgniter\API\ResponseTrait;
class ChatController extends BaseController
{

    use ResponseTrait;
    protected $db;
    protected $chatModel;
    protected $chatUsuarioModel;
    protected $mensajeModel;

    public function __construct()
    {
        $this->chatModel = new ChatModel();
        $this->chatUsuarioModel = new ChatUsuarioModel();
        $this->mensajeModel = new ChatMensajeModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * ================================
     * 🔵 Crear chat privado si no existe
     * ================================
     */
    public function crearPrivado()
    {
        $usuario1 = $this->request->getPost('usuario1');
        $usuario2 = $this->request->getPost('usuario2');

        if (!$usuario1 || !$usuario2) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Datos incompletos'
            ]);
        }

        // 1. Verificar si ya existe un chat privado entre ambos
        $query = $this->db->query("
    SELECT c.id
    FROM chats c
    JOIN chat_usuarios cu1 ON cu1.chat_id = c.id AND cu1.usuario_id = ?
    JOIN chat_usuarios cu2 ON cu2.chat_id = c.id AND cu2.usuario_id = ?
    WHERE c.tipo = 'privado'
    LIMIT 1
", [$usuario1, $usuario2])->getRow();



        if ($query) {
            return $this->response->setJSON([
                'status' => 'ok',
                'chat_id' => $query->id
            ]);
        }

        // 2. Crear nuevo chat
        $chatId = $this->chatModel->insert([
            'tipo' => 'privado',
            'creado_por' => $usuario1
        ]);

        // 3. Insertar participantes
        $this->chatUsuarioModel->insert([
            'chat_id' => $chatId,
            'usuario_id' => $usuario1
        ]);

        $this->chatUsuarioModel->insert([
            'chat_id' => $chatId,
            'usuario_id' => $usuario2
        ]);

        return $this->response->setJSON([
            'status' => 'ok',
            'chat_id' => $chatId
        ]);
    }

    /**
     * ================================
     * 🔵 Obtener historial del chat
     * ================================
     */
    public function historial($chatId)
    {
        $usuarioId = session('id');

        // Validar que pertenece al chat
        if (!$this->chatModel->usuarioEnChat($chatId, $usuarioId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No tienes permiso en este chat'
            ]);
        }

        // 🔵 1. Marcar como leídos los mensajes que NO son míos
        $this->db->table('chat_mensajes')
            ->where('chat_id', $chatId)
            ->where('emisor_id !=', $usuarioId)
            ->set('estado', 1)
            ->update();

        // 🔵 2. Obtener historial
        $mensajes = $this->mensajeModel->obtenerMensajesDeChat($chatId);

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $mensajes
        ]);
    }


    /**
     * ================================
     * 🔵 Enviar mensaje cifrado
     * ================================
     */
    public function enviar()
    {
        $chatId = $this->request->getPost('chat_id');
        $emisorId = session('id');
        $mensaje = $this->request->getPost('mensaje');
        $tipo = $this->request->getPost('tipo') ?? 'texto';
        $archivo = $this->request->getPost('archivo_url');

        if (!$chatId || !$emisorId || (!$mensaje && !$archivo)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Datos incompletos'
            ]);
        }

        if (!$this->chatModel->usuarioEnChat($chatId, $emisorId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No perteneces a este chat'
            ]);
        }

        // Guardar mensaje en BD (cifrado)
        $this->mensajeModel->guardarMensaje([
            'chat_id' => $chatId,
            'emisor_id' => $emisorId,
            'mensaje' => $mensaje, // texto plano
            'tipo' => $tipo,
            'archivo_url' => $archivo,
            'estado' => 0
        ]);



        // Mensaje para devolver
        $msg = [
            'chat_id' => $chatId,
            'emisor_id' => $emisorId,
            'mensaje' => $mensaje,
            'tipo' => $tipo,
            'archivo_url' => $archivo,
            'estado' => 0,
            'participantes' => $this->chatUsuarioModel->obtenerIdsDeUsuarios($chatId),
            'fecha' => date('Y-m-d H:i:s')
        ];

        return $this->response->setJSON([
            'status' => 'ok',
            'msg' => $msg
        ]);
    }

    public function recientes()
    {
        $usuarioId = session('id');

        // chats donde participa
        $chats = $this->chatModel->obtenerChatsDeUsuario($usuarioId);

        $resultado = [];

        foreach ($chats as $chat) {
            $ultimo = $this->mensajeModel->obtenerUltimoMensaje($chat['id']);

            $resultado[] = [
                'chat_id' => $chat['id'],
                'tipo' => $chat['tipo'],
                'ultimo_mensaje' => $ultimo['mensaje'] ?? '',
                'tipo_mensaje' => $ultimo['tipo'] ?? 'texto',
                'hora' => $ultimo['enviado_en'] ?? null,
                'estado' => $ultimo['estado'] ?? 0,
                'con' => $this->chatUsuarioModel->obtenerOtroParticipante($chat['id'], $usuarioId)
            ];
        }

        return $this->response->setJSON([
            'status' => 'ok',
            'data' => $resultado
        ]);
    }

    public function enviarArchivo()
    {
        $chatId = $this->request->getPost('chat_id');
        $emisorId = session('id');
        $archivo = $this->request->getFile('archivo');
        $mensajeTexto = $this->request->getPost('mensaje') ?? '';

        if (!$chatId || !$emisorId || !$archivo) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Datos incompletos'
            ]);
        }

        if (!$this->chatModel->usuarioEnChat($chatId, $emisorId)) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'No perteneces al chat'
            ]);
        }

        if (!$archivo->isValid()) {
            return $this->response->setJSON([
                'status' => 'error',
                'msg' => 'Archivo inválido'
            ]);
        }

        // ============================================================
        // ✔ 1. Carpeta por chat → public/uploads/chat/{chatId}/
        // ============================================================
        $ruta = FCPATH . "uploads/chat/$chatId/";

        if (!is_dir($ruta)) {
            mkdir($ruta, 0777, true);
        }

        // ============================================================
        // ✔ 2. Obtener nombre original y extensión
        // ============================================================
        $nombreOriginal = $archivo->getName();
        $extension = $archivo->getExtension();
        $nombreBase = pathinfo($nombreOriginal, PATHINFO_FILENAME);

        // ============================================================
        // ✔ 3. Verificar si existe un archivo con el mismo nombre
        //     y aplicar sufijo _1, _2, _3 ...
        // ============================================================
        $nombreFinal = $nombreOriginal;
        $contador = 1;

        while (file_exists($ruta . $nombreFinal)) {
            $nombreFinal = $nombreBase . "_" . $contador . "." . $extension;
            $contador++;
        }

        // ============================================================
        // ✔ 4. Mover archivo al destino final
        // ============================================================
        $archivo->move($ruta, $nombreFinal);

        // URL pública correcta
        $url = base_url("uploads/chat/$chatId/" . $nombreFinal);

        // ============================================================
        // ✔ 5. Guardar en BD
        // ============================================================
        $this->mensajeModel->guardarMensaje([
            'chat_id' => $chatId,
            'emisor_id' => $emisorId,
            'mensaje' => $mensajeTexto,
            'tipo' => 'archivo',
            'archivo_url' => $url,
            'estado' => 0
        ]);

        $msg = [
            'chat_id' => $chatId,
            'emisor_id' => $emisorId,
            'mensaje' => $mensajeTexto,
            'tipo' => 'archivo',
            'archivo_url' => $url,
            'estado' => 0,
            'participantes' => $this->chatUsuarioModel->obtenerIdsDeUsuarios($chatId),
            'fecha' => date('Y-m-d H:i:s')
        ];

        return $this->response->setJSON([
            'status' => 'ok',
            'msg' => $msg
        ]);
    }

    public function marcarLeidos($chatId)
    {
        $usuarioId = session('id');

        return $this->db->table('chat_mensajes')
            ->where('chat_id', $chatId)
            ->where('emisor_id !=', $usuarioId)
            ->set('estado', 1)
            ->update();
    }

    public function contarNoLeidos()
    {
        $usuarioId = session('id');

        $sql = "
        SELECT COUNT(*) AS total
        FROM chat_mensajes m
        JOIN chat_usuarios cu ON cu.chat_id = m.chat_id
        WHERE cu.usuario_id = :uid:
        AND m.emisor_id != :uid:
        AND m.estado = 0
    ";

        $row = $this->db->query($sql, [
            'uid' => $usuarioId
        ])->getRowArray();

        return $this->response->setJSON([
            'status' => 'ok',
            'total' => intval($row['total'])
        ]);
    }

}
