<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CicloAcademicoModel;

class CiclosController extends BaseController
{
    protected $cicloModel;

    public function __construct()
    {
        $this->cicloModel = new CicloAcademicoModel();
    }

    public function index()
    {
        $data['ciclos'] = $this->cicloModel->orderBy('id', 'ASC')->findAll();
        return view('lms/admin/ciclos/index', $data);
    }

    public function crear()
    {
        $this->cicloModel->insert([
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'num_parciales' => $this->request->getPost('num_parciales'),
            'duracion_meses' => $this->request->getPost('duracion_meses'),
            'activo' => $this->request->getPost('activo') ? 1 : 0,
        ]);

        return redirect()->back()->with('msg', 'Ciclo académico creado correctamente');
    }

    public function eliminar($id)
    {
        $this->cicloModel->delete($id);
        return redirect()->back()->with('msg', 'Ciclo eliminado correctamente');
    }

    public function cambiarEstado($id)
    {
        $ciclo = $this->cicloModel->find($id);
        if ($ciclo) {
            $nuevoEstado = $ciclo['activo'] ? 0 : 1;
            $this->cicloModel->update($id, ['activo' => $nuevoEstado]);
        }
        return redirect()->back()->with('msg', 'Estado actualizado');
    }
}
