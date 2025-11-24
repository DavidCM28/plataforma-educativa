<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\ProyectoModel;
use App\Models\ProyectoArchivoModel;
use App\Models\PublicacionModel;
use App\Models\CriterioEvaluacionModel;
use App\Models\PonderacionCicloModel;

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
    // 📄 Vista parcial (AJAX)
    // ============================================================
    public function index($asignacionId)
    {
        $criterioModel = new CriterioEvaluacionModel();
        $criterios = $criterioModel->where('activo', 1)->findAll();

        return view('lms/profesor/grupos/proyectos', [
            'asignacionId' => $asignacionId,
            'criterios' => $criterios
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
    // 💾 Crear o actualizar proyecto (+ publicación automática)
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
            'parcial_numero' => $data['parcial_numero'] ?? 1,
            'criterio_id' => $data['criterio_id'] ?? null,
            'porcentaje_proyecto' => $data['porcentaje_proyecto'] ?? 0,
        ];

        // ✅ Insertar o actualizar
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

        // 📰 Crear publicación automática
        try {
            date_default_timezone_set('America/Mexico_City');

            $urlProyecto = base_url('profesor/proyectos/ver/' . $proyectoId);

            $contenido = "
            <div class='aviso-tarea'>
                <p>🚀 El profesor ha {$accion} un nuevo proyecto: <b>{$proyectoData['titulo']}</b>.</p>
                " . (!empty($proyectoData['fecha_entrega']) ? "<p>📅 Fecha de entrega: <b>" . date('d/m/Y H:i', strtotime($proyectoData['fecha_entrega'])) . "</b></p>" : "") . "
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
    // 📘 Obtener detalles de un proyecto
    // ============================================================
    public function detalle($id)
    {
        $proyecto = $this->proyectoModel->obtenerConArchivos($id);

        if (!$proyecto) {
            return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);
        }

        return $this->response->setJSON($proyecto);
    }

    // ============================================================
    // 🗑️ Eliminar proyecto completo (+ publicación aviso)
    // ============================================================
    public function eliminar($id)
    {
        $proyecto = $this->proyectoModel->find($id);
        if (!$proyecto) {
            return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);
        }

        $archivos = $this->archivoModel->obtenerPorProyecto($id);
        foreach ($archivos as $a) {
            $ruta = FCPATH . 'uploads/proyectos/' . $a['archivo'];
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }

        $this->archivoModel->eliminarPorProyecto($id);
        $this->proyectoModel->delete($id);

        // 📣 Publicación de eliminación
        try {
            $this->publicacionModel->insert([
                'grupo_materia_profesor_id' => $proyecto['asignacion_id'],
                'usuario_id' => session('id') ?? session('usuario_id') ?? session('id_usuario'),
                'tipo' => 'aviso',
                'contenido' => "🗑️ El profesor ha eliminado el proyecto <b>{$proyecto['titulo']}</b>.",
                'fecha_publicacion' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al registrar publicación de eliminación: ' . $e->getMessage());
        }

        return $this->response->setJSON(['success' => true, 'mensaje' => 'Proyecto y archivos eliminados correctamente.']);
    }

    // ============================================================
    // 🗑️ Eliminar un archivo específico de un proyecto
    // ============================================================
    public function eliminarArchivo($id)
    {
        $archivo = $this->archivoModel->find($id);
        if (!$archivo) {
            return $this->response->setJSON(['error' => 'Archivo no encontrado.']);
        }

        $ruta = FCPATH . 'uploads/proyectos/' . $archivo['archivo'];
        if (is_file($ruta)) {
            @unlink($ruta);
        }

        $this->archivoModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Archivo eliminado correctamente.'
        ]);
    }

    // ============================================================
    // 📊 Obtener porcentaje disponible de criterio / parcial
    // ============================================================
    public function obtenerPorcentajeCriterio()
    {
        $criterioId = $this->request->getGet('criterio_id');
        $parcialNum = $this->request->getGet('parcial_num');
        $cicloId = $this->request->getGet('ciclo_id') ?? session('ciclo_id');

        if (!$criterioId || !$parcialNum) {
            return $this->response->setJSON(['error' => 'Faltan parámetros.']);
        }

        $ponderacionModel = new PonderacionCicloModel();

        $registro = $ponderacionModel
            ->where('criterio_id', $criterioId)
            ->where('parcial_num', $parcialNum)
            ->where('ciclo_id', $cicloId)
            ->first();

        if (!$registro) {
            return $this->response->setJSON([
                'porcentaje' => 0,
                'mensaje' => 'Este criterio no tiene ponderación definida para el parcial seleccionado.'
            ]);
        }

        return $this->response->setJSON(['porcentaje' => (float) $registro['porcentaje']]);
    }

    // ============================================================
    // 📊 Calcular porcentaje ya usado en ese criterio/parcial
    // ============================================================
    public function obtenerPorcentajeUsado()
    {
        $criterioId = $this->request->getGet('criterio_id');
        $parcialNum = $this->request->getGet('parcial_num');
        $asignacionId = $this->request->getGet('asignacion_id');
        $proyectoId = $this->request->getGet('proyecto_id'); // excluir actual

        if (!$criterioId || !$parcialNum || !$asignacionId) {
            return $this->response->setJSON(['error' => 'Faltan parámetros']);
        }

        $db = \Config\Database::connect();

        $builder = $db->table('proyectos')
            ->selectSum('porcentaje_proyecto', 'total_usado')
            ->where('criterio_id', $criterioId)
            ->where('parcial_numero', $parcialNum)
            ->where('asignacion_id', $asignacionId);

        if ($proyectoId) {
            $builder->where('id !=', $proyectoId);
        }

        $resultado = $builder->get()->getRowArray();
        $usado = (float) ($resultado['total_usado'] ?? 0);

        return $this->response->setJSON(['usado' => $usado]);
    }

    // ============================================================
    // 👁️ Vista individual de proyecto
    // ============================================================
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

    // ============================================================
// 👁️ Vista de entregas (interfaz completa)
// ============================================================
    public function vistaEntregas($proyectoId)
    {
        $proyecto = $this->proyectoModel->find($proyectoId);
        if (!$proyecto) {
            return redirect()->back()->with('error', 'Proyecto no encontrado.');
        }

        return view('lms/profesor/grupos/proyectos_entregas', [
            'proyectoId' => $proyectoId,
            'proyecto' => $proyecto
        ]);
    }


    // ============================================================
// 📋 Listar entregas (alumnos con y sin entrega)
// ============================================================
    public function listarEntregas($proyectoId)
    {
        try {
            $entregaModel = new \App\Models\EntregaProyectoModel();
            $grupoMateriaProfesorModel = new \App\Models\GrupoMateriaProfesorModel();
            $db = \Config\Database::connect();

            // 🔍 Buscar el proyecto
            $proyecto = $this->proyectoModel->find($proyectoId);
            if (!$proyecto) {
                return $this->response->setJSON(['error' => 'Proyecto no encontrado.']);
            }

            // 🔎 Determinar grupo_id a partir de la asignación
            $grupoId = null;
            if (!empty($proyecto['asignacion_id'])) {
                $asignacion = $grupoMateriaProfesorModel->find($proyecto['asignacion_id']);
                if ($asignacion) {
                    $grupoId = $asignacion['grupo_id'];
                }
            }

            if (!$grupoId) {
                return $this->response->setJSON(['error' => 'No se pudo determinar el grupo asociado al proyecto.']);
            }

            // 👥 Obtener alumnos inscritos en el grupo
            $alumnos = $db->table('grupo_alumno ga')
                ->select('u.id, u.nombre, u.apellido_paterno, u.apellido_materno')
                ->join('usuarios u', 'u.id = ga.alumno_id')
                ->where('ga.grupo_id', $grupoId)
                ->get()
                ->getResultArray();

            // 📦 Entregas del proyecto
            $entregas = $entregaModel->where('proyecto_id', $proyectoId)->findAll();

            // 🔗 Asociar entrega y estado a cada alumno
            foreach ($alumnos as &$a) {
                $entrega = array_values(array_filter($entregas, fn($e) => $e['alumno_id'] == $a['id']));
                if ($entrega) {
                    $ent = end($entrega);
                    $a['entrega'] = $ent;
                    $a['estado'] = $entregaModel->obtenerEstadoAlumno($proyectoId, $a['id']);
                    // fusionar datos de entrega a nivel raíz (para JS)
                    $a = array_merge($a, $ent);
                } else {
                    $a['entrega'] = null;
                    $a['estado'] = 'pendiente';
                }
            }

            return $this->response->setJSON([
                'proyecto' => $proyecto,
                'alumnos' => $alumnos
            ]);

        } catch (\Throwable $e) {
            log_message('error', 'Error en listarEntregasProyecto: ' . $e->getMessage());
            return $this->response->setJSON(['error' => $e->getMessage()]);
        }
    }


    // ============================================================
// 🧾 Guardar calificación y retroalimentación
// ============================================================
    public function calificar($entregaId)
    {
        $entregaModel = new \App\Models\EntregaProyectoModel();
        $data = [
            'calificacion' => $this->request->getPost('calificacion'),
            'retroalimentacion' => trim($this->request->getPost('comentarios') ?? '')
        ];

        if (!$entregaModel->find($entregaId)) {
            return $this->response->setJSON(['error' => 'Entrega no encontrada.']);
        }

        $entregaModel->update($entregaId, $data);

        return $this->response->setJSON([
            'success' => true,
            'mensaje' => 'Calificación guardada correctamente.'
        ]);
    }




}
