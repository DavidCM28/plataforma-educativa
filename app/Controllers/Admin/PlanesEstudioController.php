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
        $data['planes'] = $this->planModel->withCarrera()->orderBy('planes_estudio.id', 'ASC')->findAll();
        $data['carreras'] = $this->carreraModel->where('activo', 1)->findAll();
        $data['materias'] = $this->materiaModel->where('activo', 1)->findAll();

        // Duraciones por carrera
        $duraciones = [];
        foreach ($data['carreras'] as $carrera) {
            $duraciones[$carrera['id']] = (int) ($carrera['duracion'] ?? 10);
        }
        $data['duracionesPorCarrera'] = $duraciones;

        return view('lms/admin/planes_estudio/index', $data);
    }

    // 🟠 Crear nuevo plan
    public function crear()
    {
        try {
            $data = [
                'carrera_id' => $this->request->getPost('carrera_id'),
                'nombre' => $this->request->getPost('nombre'),
                'fecha_vigencia' => $this->request->getPost('fecha_vigencia'),
                'activo' => $this->request->getPost('activo') ?? 1
            ];

            $this->planModel->insert($data);
            $insertId = $this->planModel->getInsertID();

            // ✅ Responder JSON si es AJAX
            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON([
                    'ok' => true,
                    'msg' => 'Plan de estudios creado correctamente.',
                    'id' => $insertId
                ]);
            }

            // Flujo normal (sin fetch)
            return redirect()->to(base_url('admin/planes-estudio'))
                ->with('msg', 'Plan de estudios creado correctamente.');
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Error al crear el plan: ' . $e->getMessage()
            ]);
        }
    }

    // 🟡 Actualizar plan existente
    public function actualizar($id = null)
    {
        if (!$id || !$this->planModel->find($id)) {
            $msg = 'El plan de estudios no existe.';
            return $this->response->setJSON(['ok' => false, 'msg' => $msg]);
        }

        try {
            $data = [
                'carrera_id' => $this->request->getPost('carrera_id'),
                'nombre' => $this->request->getPost('nombre'),
                'fecha_vigencia' => $this->request->getPost('fecha_vigencia'),
                'activo' => $this->request->getPost('activo') ?? 1
            ];

            $this->planModel->update($id, $data);

            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON([
                    'ok' => true,
                    'msg' => 'Plan de estudios actualizado correctamente.'
                ]);
            }

            return redirect()->to(base_url('admin/planes-estudio'))
                ->with('msg', 'Plan de estudios actualizado correctamente.');
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Error al actualizar el plan: ' . $e->getMessage()
            ]);
        }
    }

    // 🔵 Asignar materia a plan
    public function agregarMateria()
    {
        try {
            log_message('debug', 'PLAN_ID recibido: ' . $this->request->getPost('plan_id'));

            $data = [
                'plan_id' => $this->request->getPost('plan_id'),
                'materia_id' => $this->request->getPost('materia_id'),
                'cuatrimestre' => $this->request->getPost('cuatrimestre'),
                'tipo' => $this->request->getPost('tipo')
            ];

            $this->planMateriaModel->insert($data);

            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON([
                    'ok' => true,
                    'msg' => 'Materia asignada correctamente al plan.'
                ]);
            }

            return redirect()->to(base_url('admin/planes-estudio'))
                ->with('msg', 'Materia asignada correctamente al plan.');
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Error al asignar materia: ' . $e->getMessage()
            ]);
        }
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
        if (!$id) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'ID inválido.']);
        }

        try {
            $this->planMateriaModel->delete($id);
            return $this->response->setJSON(['ok' => true, 'msg' => 'Materia eliminada del plan.']);
        } catch (\Throwable $e) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ❌ Eliminar plan de estudio
    public function eliminar($id)
    {
        if (!$id || !$this->planModel->find($id)) {
            $msg = 'El plan de estudios no existe.';
            return $this->response->setJSON(['ok' => false, 'msg' => $msg]);
        }

        try {
            $this->planModel->delete($id);

            if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
                return $this->response->setJSON([
                    'ok' => true,
                    'msg' => 'Plan de estudios eliminado correctamente.'
                ]);
            }

            return redirect()->to(base_url('admin/planes-estudio'))
                ->with('msg', 'Plan de estudios eliminado correctamente.');
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'Error al eliminar el plan: ' . $e->getMessage()
            ]);
        }
    }
}
