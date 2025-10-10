<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PlanEstudioModel;
use App\Models\PlanMateriaModel;
use App\Models\CarreraModel;
use App\Models\MateriaModel;

class PlanesEstudioController extends BaseController
{
    protected $planModel;
    protected $planMateriaModel;
    protected $carreraModel;
    protected $materiaModel;

    public function __construct()
    {
        $this->planModel = new PlanEstudioModel();
        $this->planMateriaModel = new PlanMateriaModel();
        $this->carreraModel = new CarreraModel();
        $this->materiaModel = new MateriaModel();
        helper(['form', 'url']);
    }

    // 🟢 Vista principal
    public function index()
    {
        $data['planes'] = $this->planModel->withCarrera()->orderBy('id', 'ASC')->findAll();
        $data['carreras'] = $this->carreraModel->where('activo', 1)->findAll();
        $data['materias'] = $this->materiaModel->where('activo', 1)->findAll();
        return view('lms/admin/planes_estudio/index', $data);
    }

    // 🟠 Crear nuevo plan
    public function crear()
    {
        $data = [
            'carrera_id' => $this->request->getPost('carrera_id'),
            'nombre' => $this->request->getPost('nombre'),
            'fecha_vigencia' => $this->request->getPost('fecha_vigencia'),
            'activo' => $this->request->getPost('activo') ?? 1
        ];

        $this->planModel->insert($data);
        return redirect()->to(base_url('admin/planes-estudio'))
            ->with('msg', 'Plan de estudios creado correctamente.');
    }

    // 🟡 Asignar materia a plan
    public function agregarMateria()
    {
        $data = [
            'plan_id' => $this->request->getPost('plan_id'),
            'materia_id' => $this->request->getPost('materia_id'),
            'cuatrimestre' => $this->request->getPost('cuatrimestre'),
            'tipo' => $this->request->getPost('tipo')
        ];

        $this->planMateriaModel->insert($data);
        return redirect()->to(base_url('admin/planes-estudio'))
            ->with('msg', 'Materia asignada correctamente al plan.');
    }

    // 📄 Obtener materias de un plan (para AJAX)
    public function materiasPorPlan($id)
    {
        $materias = $this->planMateriaModel->getMateriasPorPlan($id);
        return $this->response->setJSON($materias);
    }

    // ❌ Eliminar materia de plan
    public function eliminarMateria($id)
    {
        $this->planMateriaModel->delete($id);
        return $this->response->setStatusCode(200);
    }

}
