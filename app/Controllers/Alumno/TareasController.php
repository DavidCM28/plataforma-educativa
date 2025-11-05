<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use App\Models\TareaArchivoModel;
use App\Models\EntregaTareaModel;

class TareasController extends BaseController
{
    protected $tareaModel;
    protected $archivoModel;
    protected $entregaModel;

    public function __construct()
    {
        $this->tareaModel = new TareaModel();
        $this->archivoModel = new TareaArchivoModel();
        $this->entregaModel = new EntregaTareaModel();
    }

    // ============================================================
    // 📋 Listar tareas del grupo/materia
    // ============================================================
    public function listar($asignacionId)
    {
        $alumnoId = session('id');
        $tareas = $this->tareaModel->obtenerPorAsignacion($asignacionId);

        $ahora = time(); // fecha actual del servidor

        foreach ($tareas as &$t) {
            $entregas = $this->entregaModel
                ->where('tarea_id', $t['id'])
                ->where('alumno_id', $alumnoId)
                ->findAll();

            if (count($entregas) > 0) {
                $ultimaEntrega = end($entregas);
                $t['estado'] = 'entregada';

                // ⚠️ Si se entregó después del límite → "tarde"
                if (
                    !empty($t['fecha_entrega']) &&
                    strtotime($ultimaEntrega['fecha_entrega']) > strtotime($t['fecha_entrega'])
                ) {
                    $t['estado'] = 'tarde';
                }
            } else {
                // 🕒 No entregada todavía
                $t['estado'] = 'pendiente';

                // ⚠️ Si ya pasó la fecha límite → "vencida"
                if (!empty($t['fecha_entrega']) && strtotime($t['fecha_entrega']) < $ahora) {
                    $t['estado'] = 'vencida';
                }
            }
        }

        return $this->response->setJSON($tareas);
    }


    // ============================================================
    // 📘 Obtener detalles de una tarea
    // ============================================================
    public function detalle($id)
    {
        $alumnoId = session('id');
        $tarea = $this->tareaModel->obtenerConArchivos($id);

        if (!$tarea) {
            return $this->response->setJSON(['error' => 'Tarea no encontrada.']);
        }

        // Buscar entregas del alumno
        $entregas = $this->entregaModel
            ->where('tarea_id', $id)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        $estado = 'pendiente';
        $ultimaEntrega = null;

        if (count($entregas) > 0) {
            $ultimaEntrega = end($entregas);
            $estado = 'entregada';
            if (
                !empty($tarea['fecha_entrega']) &&
                strtotime($ultimaEntrega['fecha_entrega']) > strtotime($tarea['fecha_entrega'])
            ) {
                $estado = 'tarde';
            }
        }

        $tarea['mi_entrega'] = [
            'estado' => $estado,
            'fecha_entrega' => $ultimaEntrega['fecha_entrega'] ?? null,
            'archivos' => array_map(fn($e) => $e['archivo'], $entregas),
        ];

        if ($ultimaEntrega) {
            $tarea['mi_entrega']['calificacion'] = $ultimaEntrega['calificacion'] ?? null;
            $tarea['mi_entrega']['retroalimentacion'] = $ultimaEntrega['retroalimentacion'] ?? null;
        }


        return $this->response->setJSON($tarea);
    }

    // ============================================================
    // 📤 Entregar tarea (subir archivos)
    // ============================================================
    public function entregar()
    {
        $tareaId = $this->request->getPost('tarea_id');
        $archivos = $this->request->getFiles();
        $alumnoId = session('id');

        if (!$tareaId || empty($archivos['archivos'])) {
            return $this->response->setJSON([
                'error' => 'Faltan datos o no se seleccionaron archivos.'
            ]);
        }

        $ruta = FCPATH . 'uploads/entregas/';
        if (!is_dir($ruta))
            mkdir($ruta, 0777, true);

        $guardados = 0;

        foreach ($archivos['archivos'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                // ✅ 1. Tomamos el nombre original
                $originalName = $file->getClientName();

                // ✅ 2. Limpiamos el nombre (evita espacios y caracteres extraños)
                $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);

                // ✅ 3. Si ya existe un archivo con ese nombre, agrega un sufijo único
                $finalName = $safeName;
                $contador = 1;
                while (file_exists($ruta . $finalName)) {
                    $info = pathinfo($safeName);
                    $finalName = $info['filename'] . "_{$contador}." . $info['extension'];
                    $contador++;
                }

                // ✅ 4. Mover con el nombre final
                $file->move($ruta, $finalName);

                // ✅ 5. Guardar registro en la base de datos
                $this->entregaModel->insert([
                    'tarea_id' => $tareaId,
                    'alumno_id' => $alumnoId,
                    'archivo' => $finalName,
                    'fecha_entrega' => date('Y-m-d H:i:s')
                ]);

                $guardados++;
            }
        }

        // 🔄 Recalcular estado
        $tarea = $this->tareaModel->find($tareaId);
        $entregas = $this->entregaModel
            ->where('tarea_id', $tareaId)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        $estado = 'entregada';
        if (!empty($tarea['fecha_entrega'])) {
            foreach ($entregas as $e) {
                if (strtotime($e['fecha_entrega']) > strtotime($tarea['fecha_entrega'])) {
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
// 🔄 Deshacer entrega (eliminar archivos del alumno)
// ============================================================
    public function deshacerEntrega($tareaId)
    {
        $alumnoId = session('id');

        $entregas = $this->entregaModel
            ->where('tarea_id', $tareaId)
            ->where('alumno_id', $alumnoId)
            ->findAll();

        if (empty($entregas)) {
            return $this->response->setJSON(['error' => 'No tienes entregas registradas.']);
        }

        foreach ($entregas as $e) {
            $rutaArchivo = FCPATH . 'uploads/entregas/' . $e['archivo'];
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
