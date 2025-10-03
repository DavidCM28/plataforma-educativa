<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use Cloudinary\Cloudinary;
use App\Models\UsuarioDetalleModel;

class Perfil extends BaseController
{
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión primero.');
        }

        $usuarioModel = new UsuarioModel();
        $usuarioDetalleModel = new UsuarioDetalleModel();

        $usuario = $usuarioModel->find(session('id'));
        $detalles = $usuarioDetalleModel
            ->where('usuario_id', session('id'))
            ->asArray() // 👈 asegura que devuelva array
            ->first();

        return view('lms/perfil/index', [
            'title' => 'Mi Perfil',
            'usuario' => $usuario,
            'detalles' => $detalles ?? [] // ✅ pasar detalles a la vista
        ]);
    }

    // 📸 Actualizar foto de perfil
    public function actualizarFoto()
    {
        $file = $this->request->getFile('foto');
        if ($file && $file->isValid()) {
            $newName = $file->getRandomName();
            $file->move('uploads/perfiles', $newName);

            $usuarioModel = new UsuarioModel();
            $usuarioModel->update(session('id'), ['foto' => $newName]);

            session()->set('foto', $newName);
            return redirect()->back()->with('success', 'Foto actualizada correctamente.');
        }

        return redirect()->back()->with('error', 'No se pudo subir la imagen.');
    }

    public function subirFotoCloud()
    {
        helper(['form']);
        $file = $this->request->getFile('foto');

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No se recibió ninguna imagen válida.'
            ]);
        }

        // ✅ Configuración Cloudinary
        $cloudinary = new \Cloudinary\Cloudinary([
            'cloud' => [
                'cloud_name' => 'dgxwfohv4',
                'api_key' => '653319182536276',
                'api_secret' => 'ijhWz9MUKJAX7cgLaLMCbTdu9ok'
            ],
            'url' => ['secure' => true]
        ]);

        $usuarioModel = new \App\Models\UsuarioModel();
        $usuario = $usuarioModel->find(session('id'));

        try {
            // 🧹 Si ya tiene una foto anterior en Cloudinary, eliminarla
            if (!empty($usuario['foto']) && str_contains($usuario['foto'], 'res.cloudinary.com')) {
                // Extraer el public_id
                $publicId = basename(parse_url($usuario['foto'], PHP_URL_PATH)); // ejemplo: perfiles/abc123.jpg
                $publicId = preg_replace('/\.[^.]+$/', '', $publicId); // quitar extensión
                $publicId = 'perfiles/' . $publicId;

                $cloudinary->uploadApi()->destroy($publicId, ['invalidate' => true]);
            }

            // 📤 Subir nueva imagen
            $upload = $cloudinary->uploadApi()->upload(
                $file->getTempName(),
                [
                    'folder' => 'perfiles',
                    'overwrite' => true,
                    'resource_type' => 'image'
                ]
            );

            $url = $upload['secure_url'];

            // 🧠 Guardar en base de datos
            $usuarioModel->update(session('id'), ['foto' => $url]);
            session()->set('foto', $url);

            return $this->response->setJSON([
                'success' => true,
                'url' => $url
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al subir o eliminar imagen: ' . $e->getMessage()
            ]);
        }
    }

    // 🔒 Cambiar contraseña
    public function actualizarPassword()
    {
        $password = $this->request->getPost('password');
        $confirmar = $this->request->getPost('confirmar');

        if ($password !== $confirmar) {
            return redirect()->back()->with('error', 'Las contraseñas no coinciden.');
        }

        $usuarioModel = new UsuarioModel();
        $usuarioModel->update(session('id'), [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success', 'Contraseña actualizada correctamente.');
    }

    public function guardarDetalles()
    {
        $usuarioDetalleModel = new UsuarioDetalleModel();
        $usuarioId = session('id');

        $data = $this->request->getPost([
            'sexo',
            'fecha_nacimiento',
            'estado_civil',
            'curp',
            'rfc',
            'pais_origen',
            'peso',
            'estatura',
            'tipo_sangre',
            'antecedente_diabetico',
            'antecedente_hipertenso',
            'antecedente_cardiaco',
            'estado',
            'municipio',
            'colonia',
            'calle',
            'numero_exterior',
            'numero_interior',
            'telefono',
            'correo_alternativo',
            'telefono_trabajo',
            'grado_academico',
            'descripcion_grado',
            'cedula_profesional'
        ]);

        // Normalizar checkboxes
        $data['antecedente_diabetico'] = $this->request->getPost('antecedente_diabetico') ? 1 : 0;
        $data['antecedente_hipertenso'] = $this->request->getPost('antecedente_hipertenso') ? 1 : 0;
        $data['antecedente_cardiaco'] = $this->request->getPost('antecedente_cardiaco') ? 1 : 0;
        $data['usuario_id'] = $usuarioId;

        if ($usuarioDetalleModel->existeDetalle($usuarioId)) {
            $usuarioDetalleModel->where('usuario_id', $usuarioId)->set($data)->update();
        } else {
            $usuarioDetalleModel->insert($data);
        }

        // ✅ Devuelve JSON si es petición AJAX
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Información personal actualizada correctamente.'
            ]);
        }

        // Fallback si es POST tradicional
        return redirect()->back()->with('success', 'Información personal actualizada correctamente.');
    }

}
