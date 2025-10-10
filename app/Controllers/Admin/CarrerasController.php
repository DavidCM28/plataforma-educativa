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

    public function crear()
    {
        $this->carreraModel->save([
            'nombre' => $this->request->getPost('nombre'),
            'siglas' => $this->request->getPost('siglas'),
            'duracion' => $this->request->getPost('duracion'),
            'activo' => 1
        ]);

        return redirect()->to(base_url('admin/carreras'))->with('msg', '✅ Carrera registrada correctamente');
    }

    public function actualizar($id)
    {
        $this->carreraModel->update($id, [
            'nombre' => $this->request->getPost('nombre'),
            'siglas' => $this->request->getPost('siglas'),
            'duracion' => $this->request->getPost('duracion'),
            'activo' => $this->request->getPost('activo') ? 1 : 0
        ]);

        return redirect()->to(base_url('admin/carreras'))->with('msg', '✏️ Carrera actualizada');
    }

    public function eliminar($id)
    {
        $this->carreraModel->delete($id);
        return redirect()->to(base_url('admin/carreras'))->with('msg', '🗑️ Carrera eliminada');
    }
}
