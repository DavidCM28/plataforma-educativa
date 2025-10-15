<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CarreraModel;

class CarrerasController extends BaseController
{
    protected $carreraModel;

    public function __construct()
    {
        $this->carreraModel = new CarreraModel();
    }

    public function index()
    {
        $data['carreras'] = $this->carreraModel->findAll();
        return view('lms/admin/carreras/index', $data);
    }

    // 🟢 Crear carrera
    public function crear()
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'siglas' => $this->request->getPost('siglas'),
            'duracion' => $this->request->getPost('duracion'),
            'activo' => 1
        ];

        $this->carreraModel->insert($data);

        // Si viene desde AJAX → responder JSON
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'msg' => '✅ Carrera registrada correctamente',
                'id' => $this->carreraModel->getInsertID()
            ]);
        }

        // Si no es AJAX → flujo tradicional
        return redirect()->to(base_url('admin/carreras'))
            ->with('msg', '✅ Carrera registrada correctamente');
    }

    // 🟡 Actualizar carrera
    public function actualizar($id)
    {
        $data = [
            'nombre' => $this->request->getPost('nombre'),
            'siglas' => $this->request->getPost('siglas'),
            'duracion' => $this->request->getPost('duracion'),
            'activo' => $this->request->getPost('activo') ? 1 : 0
        ];

        $this->carreraModel->update($id, $data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'msg' => '✏️ Carrera actualizada correctamente'
            ]);
        }

        return redirect()->to(base_url('admin/carreras'))
            ->with('msg', '✏️ Carrera actualizada');
    }

    // ❌ Eliminar carrera
    public function eliminar($id)
    {
        $this->carreraModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'msg' => '🗑️ Carrera eliminada correctamente'
            ]);
        }

        return redirect()->to(base_url('admin/carreras'))
            ->with('msg', '🗑️ Carrera eliminada');
    }
}
