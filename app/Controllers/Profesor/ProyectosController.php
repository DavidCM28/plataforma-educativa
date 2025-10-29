<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\ProyectoModel;
use App\Models\ProyectoArchivoModel;
use App\Models\PublicacionModel;

class ProyectosController extends BaseController
{
    protected $proyectoModel;
    protected $archivoModel;
    protected $publicacionModel;

    public function __construct()
    {
        $this->proyectoModel = new ProyectoModel();
        $this->archivoModel = new ProyectoArchivoModel();
        $this->publicacionModel = new PublicacionModel();
    }

    // ============================================================
    // 📄 Vista parcial
    // ============================================================
    public function index($asignacionId)
    {
        return view('lms/profesor/grupos/proyectos', [
            'asignacionId' => $asignacionId
        ]);
    }

    // ============================================================
    // 📋 Listar proyectos
    // ============================================================
    public function listar($asignacionId)
    {
        $proyectos = $this->proyectoModel->obtenerPorAsignacion($asignacionId);

        foreach ($proyectos as &$p) {
            $p['archivos'] = $this->archivoModel->obtenerPorProyecto($p['id']);
        }

        return $this->response->setJSON($proyectos);
    }

    // ============================================================
    // 💾 Crear o actualizar proyecto (+ publicación)
    // ============================================================
    public function guardar()
    {
        $data = $this->request->getPost();
        $archivos = $this->request->getFiles();

        if (empty($data['titulo']) || empty($data['asignacion_id'])) {
            return $this->response->setJSON(['error' => 'El título y la asignación son obligatorios.']);
        }

        $proyectoId = $data['id'] ?? null;
        $profesorId = session('id') ?? session('usuario_id') ?? session('id_usuario');

        $proyectoData = [
            'asignacion_id' => $data['asignacion_id'],
            'profesor_id' => $profesorId,
            'titulo' => trim($data['titulo']),
            'descripcion' => trim($data['descripcion'] ?? ''),
            'fecha_entrega' => !empty($data['fecha_entrega'])
                ? date('Y-m-d H:i:s', strtotime($data['fecha_entrega']))
                : null,
        ];

        if ($proyectoId) {
            $this->proyectoModel->update($proyectoId, $proyectoData);
            $mensaje = "Proyecto actualizado correctamente.";
            $accion = "actualizó";
        } else {
            $proyectoId = $this->proyectoModel->insert($proyectoData);
            $mensaje = "Proyecto creado correctamente.";
            $accion = "publicó";
        }

        // 📎 Guardar archivos
        if (!empty($archivos['archivos'])) {
            foreach ($archivos['archivos'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move(FCPATH . 'uploads/proyectos', $newName);

                    $this->archivoModel->insert([
                        'proyecto_id' => $proyectoId,
                        'archivo' => $newName,
                        'tipo' => $file->getClientMimeType(),
                    ]);
                }
            }
        }

        // 📰 Crear publicación en el grupo
        try {
            $urlProyecto = base_url('profesor/grupos/ver-proyecto/' . $proyectoId);


            $contenido = "
            <div class='aviso-tarea'>
                <p>🚀 El profesor ha {$accion} un nuevo proyecto: <b>{$proyectoData['titulo']}</b>.</p>
                " . (!empty($proyectoData['fecha_entrega']) ? "<p>📅 Entrega: <b>" . date('d/m/Y H:i', strtotime($proyectoData['fecha_entrega'])) . "</b></p>" : "") . "
                <a href='{$urlProyecto}' class='btn-ver-tarea'>Ver proyecto</a>
            </div>";

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

        return $this->response->setJSON(['success' => true, 'mensaje' => $mensaje]);
    }

    // ============================================================
    // 📘 Detalle
    // ============================================================
    public function detalle($id)
    {
        $proyecto = $this->proyectoModel->obtenerConArchivos($id);
        if (!$proyecto)
            return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);
        return $this->response->setJSON($proyecto);
    }

    // ============================================================
    // 🗑️ Eliminar proyecto
    // ============================================================
    public function eliminar($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        if (!$proyecto)
            return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);

        $archivos = $this->archivoModel->obtenerPorProyecto($id);
        foreach ($archivos as $a) {
            $ruta = FCPATH . 'uploads/proyectos/' . $a['archivo'];
            if (is_file($ruta))
                @unlink($ruta);
        }

        $this->archivoModel->eliminarPorProyecto($id);
        $this->proyectoModel->delete($id);

        return $this->response->setJSON(['success' => true, 'mensaje' => 'Proyecto eliminado correctamente.']);
    }

    // ============================================================
    // 🗑️ Eliminar archivo específico
    // ============================================================
    public function eliminarArchivo($id)
    {
        $archivo = $this->archivoModel->find($id);
        if (!$archivo)
            return $this->response->setJSON(['error' => 'Archivo no encontrado.']);

        $ruta = FCPATH . 'uploads/proyectos/' . $archivo['archivo'];
        if (is_file($ruta))
            @unlink($ruta);

        $this->archivoModel->delete($id);

        return $this->response->setJSON(['success' => true, 'mensaje' => 'Archivo eliminado.']);
    }

    public function ver($id)
    {
        $proyecto = $this->proyectoModel->obtenerConArchivos($id);
        if (!$proyecto) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Proyecto no encontrado.');
        }

        return view('lms/profesor/grupos/ver_proyecto', [
            'proyecto' => $proyecto
        ]);
    }

}
