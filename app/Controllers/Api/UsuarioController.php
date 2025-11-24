<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;

class UsuarioController extends BaseController
{
    public function buscar()
    {
        $q = $this->request->getGet('q');
        $idActual = session('id');

        if (!$q) {
            return $this->response->setJSON(['data' => []]);
        }

        $db = \Config\Database::connect();
        $query = $db->table('usuarios')
            ->select('id, nombre, apellido_paterno, apellido_materno, foto')
            ->groupStart()
            ->like('nombre', $q)
            ->orLike('apellido_paterno', $q)
            ->orLike('apellido_materno', $q)
            ->groupEnd()
            ->where('id !=', $idActual)
            ->limit(10)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'data' => $query
        ]);
    }
}
