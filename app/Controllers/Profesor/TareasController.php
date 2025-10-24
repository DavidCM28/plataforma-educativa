<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\TareaModel;
use App\Models\TareaArchivoModel;
use App\Models\PublicacionModel;

class TareasController extends BaseController
{
    protected $tareaModel;
    protected $archivoModel;
    protected $publicacionModel;

    public function __construct()
    {
        $this->tareaModel = new TareaModel();
        $this->archivoModel = new TareaArchivoModel();
        $this->publicacionModel = new PublicacionModel(); // Usa la tabla publicaciones_grupo
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
    // 💾 Crear o actualizar tarea (+ publicación automática)
    // ============================================================
    public function guardar()
    {
        $data = $this->request->getPost();
        $archivos = $this->request->getFiles();

        if (empty($data['titulo']) || empty($data['asignacion_id'])) {
            return $this->response->setJSON(['error' => 'El título y la asignación son obligatorios.']);
        }

        $tareaId = $data['id'] ?? null;
        $profesorId = session('id') ?? session('usuario_id') ?? session('id_usuario');

        $tareaData = [
            'asignacion_id' => $data['asignacion_id'],
            'profesor_id' => $profesorId,
            'titulo' => trim($data['titulo']),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'fecha_entrega' => !empty($data['fecha_entrega'])
                ? date('Y-m-d H:i:s', strtotime($data['fecha_entrega']))
                : null,
        ];

        // ✅ Insertar o actualizar tarea
        if ($tareaId) {
            $this->tareaModel->update($tareaId, $tareaData);
            $mensaje = "Tarea actualizada correctamente.";
            $accion = "actualizó";
        } else {
            $tareaId = $this->tareaModel->insert($tareaData);
            $mensaje = "Tarea creada correctamente.";
            $accion = "subió";
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

        // 📰 Crear publicación automática en publicaciones_grupo
        try {
            date_default_timezone_set('America/Mexico_City');

            // ⚙️ Enlace seguro con base_url y escapado de comillas
            $urlTarea = base_url('profesor/tareas/ver/' . $tareaId);

            // 📣 Contenido con diseño mejorado (usa HTML directo, sin esc())
            $contenido = "
        <div class='aviso-tarea'>
            <p>📢 El profesor ha {$accion} una nueva tarea: <b>{$tareaData['titulo']}</b>.</p>
            " . (!empty($tareaData['fecha_entrega']) ? "<p>📅 Fecha de entrega: <b>" . date('d/m/Y H:i', strtotime($tareaData['fecha_entrega'])) . "</b></p>" : "") . "
            <a href='{$urlTarea}' class='btn-ver-tarea'>Ver tarea</a>
        </div>";

            // ✅ Insertar publicación
            $this->publicacionModel->insert([
                'grupo_materia_profesor_id' => $data['asignacion_id'],
                'usuario_id' => $profesorId,
                'tipo' => 'aviso',
                'contenido' => $contenido,
                'fecha_publicacion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al crear publicación automática: ' . $e->getMessage());
        }

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => $mensaje,
        ]);
    }


    // ============================================================
    // 📘 Obtener detalles de una tarea
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
    // 🗑️ Eliminar tarea completa (+ publicación aviso)
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

        // Crear publicación informativa
        try {
            date_default_timezone_set('America/Mexico_City');

            $this->publicacionModel->insert([
                'grupo_materia_profesor_id' => $tarea['asignacion_id'],
                'usuario_id' => session('id') ?? session('usuario_id') ?? session('id_usuario'),
                'tipo' => 'aviso',
                'contenido' => "🗑️ El profesor ha eliminado la tarea <b>{$tarea['titulo']}</b>.",
                'fecha_publicacion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al registrar publicación de eliminación: ' . $e->getMessage());
        }

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

        $ruta = FCPATH . 'uploads/tareas/' . $archivo['archivo'];
        if (is_file($ruta)) {
            @unlink($ruta);
        }

        $this->archivoModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Archivo eliminado correctamente.'
        ]);
    }
}
