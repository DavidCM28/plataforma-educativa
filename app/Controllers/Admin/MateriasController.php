<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\MateriaModel;

class MateriasController extends BaseController
{
    protected $materiaModel;

    public function __construct()
    {
        $this->materiaModel = new MateriaModel();
        helper(['form', 'url']);
    }

    // 🟢 Listado principal
    public function index()
    {
        $data['materias'] = $this->materiaModel->orderBy('id', 'ASC')->findAll();
        return view('lms/admin/materias/index', $data);
    }

    // 🟠 Crear nueva materia
    public function crear()
    {
        $data = [
            'clave' => $this->request->getPost('clave'),
            'nombre' => $this->request->getPost('nombre'),
            'creditos' => $this->request->getPost('creditos'),
            'horas_semana' => $this->request->getPost('horas_semana'),
            'activo' => $this->request->getPost('activo') ?? 1,
        ];

        $this->materiaModel->insert($data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'msg' => 'Materia creada correctamente.',
                'id' => $this->materiaModel->getInsertID()
            ]);
        }

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia creada correctamente.');
    }

    // 🟡 Actualizar materia existente
    public function actualizar($id = null)
    {
        if (!$id || !$this->materiaModel->find($id)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Materia no encontrada.']);
        }

        $data = [
            'clave' => $this->request->getPost('clave'),
            'nombre' => $this->request->getPost('nombre'),
            'creditos' => $this->request->getPost('creditos'),
            'horas_semana' => $this->request->getPost('horas_semana'),
            'activo' => $this->request->getPost('activo') ?? 1,
        ];

        $this->materiaModel->update($id, $data);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'ok' => true,
                'msg' => '✏️ Materia actualizada correctamente.'
            ]);
        }

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia actualizada correctamente.');
    }

    // 🔴 Eliminar materia
    public function eliminar($id = null)
    {
        if (!$id || !$this->materiaModel->find($id)) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Materia no encontrada.']);
        }

        $this->materiaModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'msg' => 'Materia eliminada correctamente.']);
        }

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia eliminada correctamente.');
    }

    // 🧩 Validación AJAX de clave única
    public function verificarClave()
    {
        if ($this->request->isAJAX()) {
            $clave = $this->request->getGet('clave');
            $id = $this->request->getGet('id');

            if (!$clave) {
                return $this->response->setJSON(['existe' => false]);
            }

            $materia = $this->materiaModel
                ->where('clave', $clave)
                ->where('id !=', $id)
                ->first();

            return $this->response->setJSON(['existe' => $materia ? true : false]);
        }

        return $this->response->setStatusCode(400);
    }
}
