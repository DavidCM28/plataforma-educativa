<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use App\Models\TareaArchivoModel;

class TareasController extends BaseController
{
    protected $tareaModel;
    protected $archivoModel;

    public function __construct()
    {
        $this->tareaModel = new TareaModel();
        $this->archivoModel = new TareaArchivoModel();
    }

    // ============================================================
    // 📄 Vista parcial (AJAX)
    // ============================================================
    public function index($asignacionId)
    {
        return view('lms/profesor/grupos/tareas', [
            'asignacionId' => $asignacionId
        ]);
    }

    // ============================================================
    // 📋 Listar tareas
    // ============================================================
    public function listar($asignacionId)
    {
        $tareas = $this->tareaModel->obtenerPorAsignacion($asignacionId);

        foreach ($tareas as &$t) {
            $t['archivos'] = $this->archivoModel->obtenerPorTarea($t['id']);
        }

        return $this->response->setJSON($tareas);
    }

    // ============================================================
    // 💾 Crear o actualizar tarea
    // ============================================================
    public function guardar()
    {
        $data = $this->request->getPost();
        $archivos = $this->request->getFiles();

        if (empty($data['titulo']) || empty($data['asignacion_id'])) {
            return $this->response->setJSON(['error' => 'El título y la asignación son obligatorios.']);
        }

        $tareaId = $data['id'] ?? null;
        $tareaData = [
            'asignacion_id' => $data['asignacion_id'],
            'profesor_id' => session('id') ?? session('usuario_id') ?? session('id_usuario'),

            'titulo' => trim($data['titulo']),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'fecha_entrega' => !empty($data['fecha_entrega'])
                ? date('Y-m-d H:i:s', strtotime($data['fecha_entrega']))
                : null,
        ];

        if ($tareaId) {
            $this->tareaModel->update($tareaId, $tareaData);
        } else {
            $tareaId = $this->tareaModel->insert($tareaData);
        }

        // 📎 Guardar archivos adjuntos
        if (!empty($archivos['archivos'])) {
            foreach ($archivos['archivos'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/tareas', $newName);

                    $this->archivoModel->insert([
                        'tarea_id' => $tareaId,
                        'archivo' => $newName,
                        'tipo' => $file->getClientMimeType(),
                    ]);
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => $data['id'] ? 'Tarea actualizada correctamente.' : 'Tarea creada correctamente.',
        ]);
    }

    // ============================================================
    // 📘 Obtener detalles de una tarea (para edición)
    // ============================================================
    public function detalle($id)
    {
        $tarea = $this->tareaModel->obtenerConArchivos($id);

        if (!$tarea) {
            return $this->response->setJSON(['error' => 'Tarea no encontrada.']);
        }

        return $this->response->setJSON($tarea);
    }

    // ============================================================
    // 🗑️ Eliminar tarea completa
    // ============================================================
    public function eliminar($id)
    {
        $tarea = $this->tareaModel->find($id);
        if (!$tarea) {
            return $this->response->setJSON(['error' => 'Tarea no encontrada.']);
        }

        // Eliminar archivos físicos
        $archivos = $this->archivoModel->obtenerPorTarea($id);
        foreach ($archivos as $a) {
            $ruta = FCPATH . 'uploads/tareas/' . $a['archivo'];
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }

        // Borrar registros
        $this->archivoModel->eliminarPorTarea($id);
        $this->tareaModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Tarea y archivos eliminados correctamente.'
        ]);
    }

    // ============================================================
    // 🗑️ Eliminar un archivo específico de una tarea
    // ============================================================
    public function eliminarArchivo($id)
    {
        $archivo = $this->archivoModel->find($id);
        if (!$archivo) {
            return $this->response->setJSON(['error' => 'Archivo no encontrado.']);
        }

        // Eliminar archivo físico
        $ruta = FCPATH . 'uploads/tareas/' . $archivo['archivo'];
        if (is_file($ruta)) {
            @unlink($ruta);
        }

        // Eliminar de la base
        $this->archivoModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Archivo eliminado correctamente.'
        ]);
    }
}
