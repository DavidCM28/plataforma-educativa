<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CriterioEvaluacionModel;
use App\Models\PonderacionCicloModel;
use App\Models\CicloAcademicoModel;

class CriteriosController extends BaseController
{
    protected $criterioModel;
    protected $ponderacionModel;
    protected $cicloModel;

    public function __construct()
    {
        $this->criterioModel = new CriterioEvaluacionModel();
        $this->ponderacionModel = new PonderacionCicloModel();
        $this->cicloModel = new CicloAcademicoModel();
    }

    public function index()
    {
        $data = [
            'criterios' => $this->criterioModel->orderBy('id', 'ASC')->findAll(),
            'ciclos' => $this->cicloModel->where('activo', 1)->orderBy('id', 'ASC')->findAll(),
            'ponderaciones' => $this->ponderacionModel
                ->select('ponderaciones_ciclo.*, ciclos_academicos.nombre as ciclo, criterios_evaluacion.nombre as criterio')
                ->join('ciclos_academicos', 'ciclos_academicos.id = ponderaciones_ciclo.ciclo_id')
                ->join('criterios_evaluacion', 'criterios_evaluacion.id = ponderaciones_ciclo.criterio_id')
                ->orderBy('ponderaciones_ciclo.id', 'ASC')
                ->findAll(),
        ];
        return view('lms/admin/criterios/index', $data);
    }

    // CRUD de criterios
    public function crear()
    {
        $this->criterioModel->insert([
            'nombre' => $this->request->getPost('nombre'),
            'descripcion' => $this->request->getPost('descripcion'),
            'activo' => $this->request->getPost('activo') ? 1 : 0,
        ]);
        return redirect()->back()->with('msg', 'Criterio creado correctamente');
    }

    public function eliminar($id)
    {
        $this->criterioModel->delete($id);
        return redirect()->back()->with('msg', 'Criterio eliminado correctamente');
    }

    public function cambiarEstado($id)
    {
        $criterio = $this->criterioModel->find($id);
        if ($criterio) {
            $nuevo = $criterio['activo'] ? 0 : 1;
            $this->criterioModel->update($id, ['activo' => $nuevo]);
        }
        return redirect()->back()->with('msg', 'Estado actualizado');
    }

    // Ponderaciones
    public function guardarPonderacion()
    {
        $data = [
            'ciclo_id' => $this->request->getPost('ciclo_id'),
            'parcial_num' => $this->request->getPost('parcial_num'),
            'criterio_id' => $this->request->getPost('criterio_id'),
            'porcentaje' => $this->request->getPost('porcentaje'),
        ];

        $this->ponderacionModel->insert($data);

        return $this->response->setJSON(['success' => true, 'msg' => 'Ponderación guardada correctamente']);
    }

    public function listarPonderaciones($ciclo_id, $parcial_num)
    {
        $ponderaciones = $this->ponderacionModel
            ->select('ponderaciones_ciclo.id, criterios_evaluacion.nombre as criterio, ponderaciones_ciclo.porcentaje')
            ->join('criterios_evaluacion', 'criterios_evaluacion.id = ponderaciones_ciclo.criterio_id')
            ->where('ciclo_id', $ciclo_id)
            ->where('parcial_num', $parcial_num)
            ->findAll();

        return $this->response->setJSON($ponderaciones);
    }


    public function eliminarPonderacion($id)
    {
        $this->ponderacionModel->delete($id);
        return redirect()->back()->with('msg', 'Ponderación eliminada');
    }

    public function totalPonderacion($ciclo_id, $parcial_num)
    {
        $total = $this->ponderacionModel
            ->selectSum('porcentaje', 'total')
            ->where('ciclo_id', $ciclo_id)
            ->where('parcial_num', $parcial_num)
            ->first();

        return $this->response->setJSON(['total' => $total['total'] ?? 0]);
    }

    public function getParcialesPorCiclo($id)
    {
        $ciclo = $this->cicloModel->find($id);

        if (!$ciclo) {
            return $this->response->setJSON(['error' => 'Ciclo no encontrado']);
        }

        $numParciales = (int) ($ciclo['num_parciales'] ?? 0);

        return $this->response->setJSON(['num_parciales' => $numParciales]);
    }

}
