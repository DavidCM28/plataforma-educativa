<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CicloParcialModel;

class CicloParcialController extends BaseController
{
    public function buscar($cicloId, $parcial)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Método inválido']);
        }

        $model = new CicloParcialModel();

        $row = $model->where('ciclo_id', $cicloId)
            ->where('numero_parcial', $parcial)
            ->first();

        if (!$row) {
            return $this->response->setJSON([
                'error' => 'Parcial no encontrado para este ciclo.'
            ]);
        }

        return $this->response->setJSON($row);
    }
}
