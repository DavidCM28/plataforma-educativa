<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class Perfil extends BaseController
{
    public function index()
    {
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find(session('id')); // ID desde sesión

        if (!$usuario) {
            return redirect()->to('/')->with('error', 'Usuario no encontrado.');
        }

        return view('lms/perfil/index', [
            'title' => 'Mi Perfil',
            'usuario' => $usuario,
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
}
