<?php
namespace App\Controllers;

use App\Models\UsuarioModel; // Asegúrate de tener un modelo para usuarios
use CodeIgniter\Controller;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    /*public function doLogin()
    {
        $rules = [
            'correo'   => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                             ->with('error', 'Completa los campos correctamente.')
                             ->withInput();
        }

        $correo   = $this->request->getPost('correo');
        $password = $this->request->getPost('password');

        $usuario = (new UsuarioModel())->where('correo', $correo)->first();

        if (! $usuario || ! password_verify($password, $usuario['password'])) {
            return redirect()->back()
                             ->with('error', 'Credenciales incorrectas.')
                             ->withInput();
        }

        // Guardar sesión
        $session = session();
        $session->set([
            'id'     => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo'],
            'isLoggedIn' => true
        ]);

        return redirect()->to('/')->with('mensaje', 'Bienvenido de nuevo, '.$usuario['nombre']);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/')->with('mensaje', 'Sesión cerrada correctamente.');
    }*/
}
