<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatMensajeModel extends Model
{
    protected $table = 'chat_mensajes';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'chat_id',
        'emisor_id',
        'mensaje',
        'tipo',
        'archivo_url',
        'enviado_en',
        'estado'
    ];

    private $claveAES = 'CLAVE_SUPER_SECRETA_UT_2025';

    /**
     * Guardar mensaje cifrado
     */
    public function guardarMensaje($data)
    {
        $sql = "
            INSERT INTO chat_mensajes (chat_id, emisor_id, mensaje, tipo, archivo_url, estado)
            VALUES (:chat_id:, :emisor_id:, AES_ENCRYPT(:mensaje:, :clave:), :tipo:, :archivo_url:, :estado:)
        ";

        return $this->db->query($sql, [
            'chat_id' => $data['chat_id'],
            'emisor_id' => $data['emisor_id'],
            'mensaje' => $data['mensaje'],
            'clave' => $this->claveAES,
            'tipo' => $data['tipo'] ?? 'texto',
            'archivo_url' => $data['archivo_url'] ?? null,
            'estado' => $data['estado'] ?? 0
        ]);
    }

    /**
     * Obtener mensajes DESCIFRADOS
     */
    public function obtenerMensajesDeChat($chatId)
    {
        $sql = "
            SELECT 
                id,
                chat_id,
                emisor_id,
                AES_DECRYPT(mensaje, :clave:) AS mensaje,
                tipo,
                archivo_url,
                enviado_en,
                estado
            FROM chat_mensajes
            WHERE chat_id = :chat_id:
            ORDER BY enviado_en ASC
        ";

        return $this->db->query($sql, [
            'chat_id' => $chatId,
            'clave' => $this->claveAES
        ])->getResultArray();
    }

    public function obtenerUltimoMensaje($chatId)
    {
        $sql = "
        SELECT 
            AES_DECRYPT(mensaje, :clave:) AS mensaje,
            tipo,
            enviado_en,
            estado
        FROM chat_mensajes
        WHERE chat_id = :chat_id:
        ORDER BY enviado_en DESC
        LIMIT 1
        ";

        return $this->db->query($sql, [
            'chat_id' => $chatId,
            'clave' => $this->claveAES
        ])->getRowArray();
    }

}
