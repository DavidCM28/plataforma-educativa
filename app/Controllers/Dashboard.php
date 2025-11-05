<?php

namespace App\Controllers;

use App\Models\GrupoMateriaProfesorModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $session = session();

        // 🚫 Si no hay sesión, redirige al login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Debes iniciar sesión primero.');
        }

        $rol = $session->get('rol');
        $data = [
            'nombre' => $session->get('nombre'),
            'rol' => $rol,
            'permisos' => $session->get('permisos'),
        ];

        // ===============================
// 📘 CARGAR DATOS SEGÚN EL ROL
// ===============================
        switch ($rol) {
            case 'Profesor':
                return redirect()->to(base_url('profesor/dashboard'));

            case 'Alumno':
                return redirect()->to(base_url('alumno/dashboard')); // 🔹 antes mostraba una vista, ahora redirige

            case 'Superusuario':
                return view('lms/dashboard-plataforma', $data);

            default:
                return view('lms/dashboard-plataforma', $data);
        }


    }
}
