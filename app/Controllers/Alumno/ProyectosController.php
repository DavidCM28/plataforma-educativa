<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\ProyectoModel;
use App\Models\ProyectoArchivoModel;
use App\Models\EntregaProyectoModel;

class ProyectosController extends BaseController
{
    protected $proyectoModel;
    protected $archivoModel;
    protected $entregaModel;

    public function __construct()
    {
        $this->proyectoModel = new ProyectoModel();
        $this->archivoModel = new ProyectoArchivoModel();
        $this->entregaModel = new EntregaProyectoModel();
    }

    // ============================================================
    // 📋 Listar proyectos del grupo/materia
    // ============================================================
    public function listar($asignacionId)
    {
        $alumnoId = session('id');
        $proyectos = $this->proyectoModel->obtenerPorAsignacion($asignacionId);

        $ahora = time();

        foreach ($proyectos as &$p) {
            $entregas = $this->entregaModel
                ->where('proyecto_id', $p['id'])
                ->where('alumno_id', $alumnoId)
                ->findAll();

            if (count($entregas) > 0) {
                $ultimaEntrega = end($entregas);
                $p['estado'] = 'entregado';

                if (
                    !empty($p['fecha_entrega']) &&
                    strtotime($ultimaEntrega['fecha_entrega']) > strtotime($p['fecha_entrega'])
                ) {
                    $p['estado'] = 'tarde';
                }
            } else {
                $p['estado'] = 'pendiente';
                if (!empty($p['fecha_entrega']) && strtotime($p['fecha_entrega']) < $ahora) {
                    $p['estado'] = 'vencido';
                }
            }
        }

        return $this->response->setJSON($proyectos);
    }

    // ============================================================
    // 📘 Obtener detalle de un proyecto
    // ============================================================
    public function detalle($id)
    {
        $alumnoId = session('id');
        $proyecto = $this->proyectoModel->obtenerConArchivos($id);

        if (!$proyecto) {
            return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);
        }

        $entregas = $this->entregaModel
            ->where('proyecto_id', $id)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        $estado = 'pendiente';
        $ultimaEntrega = null;

        if (count($entregas) > 0) {
            $ultimaEntrega = end($entregas);
            $estado = 'entregado';
            if (
                !empty($proyecto['fecha_entrega']) &&
                strtotime($ultimaEntrega['fecha_entrega']) > strtotime($proyecto['fecha_entrega'])
            ) {
                $estado = 'tarde';
            }
        }

        $proyecto['mi_entrega'] = [
            'estado' => $estado,
            'fecha_entrega' => $ultimaEntrega['fecha_entrega'] ?? null,
            'archivos' => array_map(fn($e) => $e['archivo'], $entregas),
        ];

        if ($ultimaEntrega) {
            $proyecto['mi_entrega']['calificacion'] = $ultimaEntrega['calificacion'] ?? null;
            $proyecto['mi_entrega']['retroalimentacion'] = $ultimaEntrega['retroalimentacion'] ?? null;
        }

        return $this->response->setJSON($proyecto);
    }

    // ============================================================
    // 📤 Entregar proyecto (subir archivos)
    // ============================================================
    public function entregar()
    {
        $proyectoId = $this->request->getPost('proyecto_id');
        $archivos = $this->request->getFiles();
        $alumnoId = session('id');

        if (!$proyectoId || empty($archivos['archivos'])) {
            return $this->response->setJSON([
                'error' => 'Faltan datos o no se seleccionaron archivos.'
            ]);
        }

        $ruta = FCPATH . 'uploads/proyectos_entregas/';
        if (!is_dir($ruta))
            mkdir($ruta, 0777, true);

        $guardados = 0;

        foreach ($archivos['archivos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $originalName = $file->getClientName();
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);

                $finalName = $safeName;
                $contador = 1;
                while (file_exists($ruta . $finalName)) {
                    $info = pathinfo($safeName);
                    $finalName = $info['filename'] . "_{$contador}." . $info['extension'];
                    $contador++;
                }

                $file->move($ruta, $finalName);

                $this->entregaModel->insert([
                    'proyecto_id' => $proyectoId,
                    'alumno_id' => $alumnoId,
                    'archivo' => $finalName,
                    'fecha_entrega' => date('Y-m-d H:i:s')
                ]);

                $guardados++;
            }
        }

        // 🔄 Recalcular estado
        $proyecto = $this->proyectoModel->find($proyectoId);
        $entregas = $this->entregaModel
            ->where('proyecto_id', $proyectoId)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        $estado = 'entregado';
        if (!empty($proyecto['fecha_entrega'])) {
            foreach ($entregas as $e) {
                if (strtotime($e['fecha_entrega']) > strtotime($proyecto['fecha_entrega'])) {
                    $estado = 'tarde';
                    break;
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => "Se enviaron {$guardados} archivo(s) correctamente.",
            'estado' => $estado
        ]);
    }

    // ============================================================
    // 🔄 Deshacer entrega de proyecto
    // ============================================================
    public function deshacerEntrega($proyectoId)
    {
        $alumnoId = session('id');

        $entregas = $this->entregaModel
            ->where('proyecto_id', $proyectoId)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        if (empty($entregas)) {
            return $this->response->setJSON(['error' => 'No tienes entregas registradas.']);
        }

        foreach ($entregas as $e) {
            $rutaArchivo = FCPATH . 'uploads/proyectos_entregas/' . $e['archivo'];
            if (is_file($rutaArchivo))
                unlink($rutaArchivo);
            $this->entregaModel->delete($e['id']);
        }

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Entrega eliminada correctamente.'
        ]);
    }
}
