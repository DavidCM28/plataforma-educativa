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
        $rules = $this->materiaModel->validationRules;

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('msg', 'Error al crear la materia.');
        }

        $datos = [
            'clave' => $this->request->getPost('clave'),
            'nombre' => $this->request->getPost('nombre'),
            'creditos' => $this->request->getPost('creditos'),
            'horas_semana' => $this->request->getPost('horas_semana'),
            'activo' => $this->request->getPost('activo') ?? 1,
        ];

        $this->materiaModel->insert($datos);

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia creada correctamente.');
    }

    // 🟡 Actualizar materia existente
    public function actualizar($id = null)
    {
        if (!$id || !$this->materiaModel->find($id)) {
            return redirect()->back()->with('msg', 'Materia no encontrada.');
        }

        $rules = $this->materiaModel->getUpdateRules($id);

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('msg', 'Error al actualizar la materia.');
        }

        $datos = [
            'clave' => $this->request->getPost('clave'),
            'nombre' => $this->request->getPost('nombre'),
            'creditos' => $this->request->getPost('creditos'),
            'horas_semana' => $this->request->getPost('horas_semana'),
            'activo' => $this->request->getPost('activo') ?? 1,
        ];

        $this->materiaModel->update($id, $datos);

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia actualizada correctamente.');
    }


    // 🔴 Eliminar materia
    public function eliminar($id = null)
    {
        if (!$id || !$this->materiaModel->find($id)) {
            return redirect()->back()->with('msg', 'Materia no encontrada.');
        }

        $this->materiaModel->delete($id);

        return redirect()->to(base_url('admin/materias'))
            ->with('msg', 'Materia eliminada correctamente.');
    }

    // 🧩 Validación AJAX de clave única
    public function verificarClave()
    {
        if ($this->request->isAJAX()) {
            $clave = $this->request->getGet('clave');
            $id = $this->request->getGet('id'); // si viene desde edición

            if (!$clave) {
                return $this->response->setJSON(['existe' => false]);
            }

            $materia = $this->materiaModel
                ->where('clave', $clave)
                ->where('id !=', $id) // ignora la actual al editar
                ->first();

            return $this->response->setJSON(['existe' => $materia ? true : false]);
        }

        return $this->response->setStatusCode(400);
    }

}
