<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();

        // 🚫 Si no hay sesión, redirige al login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión primero.');
        }

        // ✅ Renderiza el dashboard con los datos del usuario
        return view('lms/dashboard-plataforma', [
            'nombre' => $session->get('nombre'),
            'rol' => $session->get('rol'),
            'permisos' => $session->get('permisos'),
        ]);
    }
}
