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
        $nombre = $this->request->getPost('nombre');
        $descripcion = $this->request->getPost('descripcion');
        $duracionMeses = (int) $this->request->getPost('duracion_meses');
        $numParciales = (int) $this->request->getPost('num_parciales');
        $fechaInicio = new \DateTime($this->request->getPost('fecha_inicio'));
        $fechaFin = new \DateTime($this->request->getPost('fecha_fin'));
        $activo = $this->request->getPost('activo') ? 1 : 0;

        // 🔍 Validación por días (más realista)
        $diff = $fechaInicio->diff($fechaFin);
        $diasReales = $diff->days;
        $mesesEstimados = round($diasReales / 30);

        if ($mesesEstimados != $duracionMeses) {
            return redirect()->back()->with('msg', '⚠️ La duración no coincide con las fechas seleccionadas.');
        }
        // Insertar ciclo
        $this->cicloModel->insert([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'num_parciales' => $numParciales,
            'duracion_meses' => $duracionMeses,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'activo' => $activo,
        ]);

        $idCiclo = $this->cicloModel->insertID();

        // Crear parciales
        $parcialesModel = new \App\Models\CicloParcialModel();

        $totalDias = $fechaInicio->diff($fechaFin)->days;
        $diasPorParcial = floor($totalDias / $numParciales);

        $inicioParcial = clone $fechaInicio;

        for ($i = 1; $i <= $numParciales; $i++) {
            $finParcial = clone $inicioParcial;
            $finParcial->modify("+$diasPorParcial days");

            if ($i == $numParciales) {
                // Último parcial termina EXACTO en fecha_fin del ciclo
                $finParcial = clone $fechaFin;
            }

            $parcialesModel->insert([
                'ciclo_id' => $idCiclo,
                'numero_parcial' => $i,
                'fecha_inicio' => $inicioParcial->format('Y-m-d'),
                'fecha_fin' => $finParcial->format('Y-m-d'),
            ]);

            $inicioParcial = clone $finParcial;
            $inicioParcial->modify("+1 day");
        }

        return redirect()->back()->with('msg', 'Ciclo académico creado correctamente');
    }

    public function actualizar()
    {
        $id = $this->request->getPost('id');
        $nombre = $this->request->getPost('nombre');
        $descripcion = $this->request->getPost('descripcion');
        $duracionMeses = (int) $this->request->getPost('duracion_meses');
        $numParciales = (int) $this->request->getPost('num_parciales');
        $fechaInicio = new \DateTime($this->request->getPost('fecha_inicio'));
        $fechaFin = new \DateTime($this->request->getPost('fecha_fin'));
        $activo = $this->request->getPost('activo') ? 1 : 0;

        // 🔍 Validación por días (más realista)
        $diff = $fechaInicio->diff($fechaFin);
        $diasReales = $diff->days;
        $mesesEstimados = round($diasReales / 30);

        if ($mesesEstimados != $duracionMeses) {
            return redirect()->back()->with('msg', '⚠️ La duración no coincide con las fechas seleccionadas.');
        }

        // 🔄 Actualizar ciclo
        $this->cicloModel->update($id, [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'num_parciales' => $numParciales,
            'duracion_meses' => $duracionMeses,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'activo' => $activo,
        ]);

        // ================================
        // 🔁 REGENERAR PARCIALES
        // ================================
        $parcialesModel = new \App\Models\CicloParcialModel();

        // Eliminar parciales anteriores
        $parcialesModel->where('ciclo_id', $id)->delete();

        // Calcular días por parcial
        $totalDias = $fechaInicio->diff($fechaFin)->days;
        $diasPorParcial = max(1, floor($totalDias / $numParciales));

        $inicioParcial = clone $fechaInicio;

        for ($i = 1; $i <= $numParciales; $i++) {
            $finParcial = clone $inicioParcial;
            $finParcial->modify("+$diasPorParcial days");

            if ($i == $numParciales) {
                // Último parcial termina EXACTO en la fecha fin del ciclo
                $finParcial = clone $fechaFin;
            }

            $parcialesModel->insert([
                'ciclo_id' => $id,
                'numero_parcial' => $i,
                'fecha_inicio' => $inicioParcial->format('Y-m-d'),
                'fecha_fin' => $finParcial->format('Y-m-d'),
            ]);

            $inicioParcial = clone $finParcial;
            $inicioParcial->modify('+1 day');
        }

        return redirect()->to(base_url('admin/ciclos'))->with('msg', 'Ciclo actualizado correctamente');
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
