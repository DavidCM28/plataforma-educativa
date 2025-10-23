<?php

namespace App\Controllers\Profesor;

use App\Controllers\BaseController;
use App\Models\PublicacionModel;

class PublicacionesController extends BaseController
{
    public function listar($asignacionId)
    {
        $model = new PublicacionModel();
        $data['publicaciones'] = $model->obtenerPorGrupo($asignacionId);
        return view('lms/profesor/grupos/publicaciones_list', $data);
    }

    public function publicar($asignacionId)
    {
        $model = new PublicacionModel();
        $contenido = trim($this->request->getPost('contenido'));

        if ($contenido === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'El contenido no puede estar vacío.']);
        }

        try {
            date_default_timezone_set('America/Mexico_City');

            $insert = $model->insert([
                'grupo_materia_profesor_id' => $asignacionId,
                'usuario_id' => session('id') ?? session('usuario_id') ?? session('id_usuario'),
                'contenido' => $contenido,
                'fecha_publicacion' => date('Y-m-d H:i:s'),
            ]);

            if ($insert) {
                return $this->response->setJSON(['success' => true]);
            } else {
                return $this->response->setJSON(['success' => false, 'error' => 'No se pudo insertar la publicación.']);
            }
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'error' => 'Error interno del servidor.',
                'detalles' => $e->getMessage(),
            ]);
        }
    }

    // 📝 EDITAR PUBLICACIÓN
    public function editar($id)
    {
        $model = new PublicacionModel();
        $publicacion = $model->find($id);

        if (!$publicacion) {
            return $this->response->setJSON(['success' => false, 'error' => 'Publicación no encontrada.']);
        }

        $usuarioActual = session('id') ?? session('usuario_id') ?? session('id_usuario');
        if ($publicacion['usuario_id'] != $usuarioActual) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permiso para editar esta publicación.']);
        }

        $nuevoContenido = trim($this->request->getPost('contenido'));
        if ($nuevoContenido === '') {
            return $this->response->setJSON(['success' => false, 'error' => 'El contenido no puede estar vacío.']);
        }

        $ok = $model->update($id, ['contenido' => $nuevoContenido]);

        return $this->response->setJSON([
            'success' => $ok,
            'error' => $ok ? null : 'No se pudo actualizar la publicación.',
        ]);
    }

    // 🗑️ ELIMINAR PUBLICACIÓN
    public function eliminar($id)
    {
        $model = new PublicacionModel();
        $publicacion = $model->find($id);

        if (!$publicacion) {
            return $this->response->setJSON(['success' => false, 'error' => 'Publicación no encontrada.']);
        }

        $usuarioActual = session('id') ?? session('usuario_id') ?? session('id_usuario');
        if ($publicacion['usuario_id'] != $usuarioActual) {
            return $this->response->setJSON(['success' => false, 'error' => 'No tienes permiso para eliminar esta publicación.']);
        }

        $ok = $model->delete($id);

        return $this->response->setJSON([
            'success' => $ok,
            'error' => $ok ? null : 'No se pudo eliminar la publicación.',
        ]);
    }
}
