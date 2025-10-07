<?php
namespace App\Controllers;

use App\Models\UsuarioModel;

class Auth extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function doLogin()
    {
        // 🔍 Depuración temporal
        // dd($this->request->getPost());

        $rules = [
            'usuario' => 'required',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->with('error', 'Debes completar todos los campos.')
                ->withInput();
        }

        $usuarioInput = trim($this->request->getPost('usuario'));
        $password = $this->request->getPost('password');

        $usuarioModel = new UsuarioModel();

        // 🔍 Buscar por matrícula o número de empleado
        $usuario = $usuarioModel
            ->groupStart()
            ->where('matricula', $usuarioInput)
            ->orWhere('num_empleado', $usuarioInput)
            ->groupEnd()
            ->first();

        if (!$usuario) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        if (!password_verify($password, $usuario['password'])) {
            return redirect()->back()->with('error', 'Contraseña incorrecta.');
        }

        if (!$usuario['activo']) {
            return redirect()->back()->with('error', 'Tu cuenta está deshabilitada.');
        }

        // === Obtener rol y permisos ===
        $db = \Config\Database::connect();
        $rol = $db->table('roles')->where('id', $usuario['rol_id'])->get()->getRowArray();

        $permisos = $db->table('rol_permisos')
            ->select('permisos.clave')
            ->join('permisos', 'rol_permisos.permiso_id = permisos.id')
            ->where('rol_permisos.rol_id', $usuario['rol_id'])
            ->get()
            ->getResultArray();

        $listaPermisos = array_column($permisos, 'clave');

        // === Guardar sesión ===
        session()->set([
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'matricula' => $usuario['matricula'],
            'num_empleado' => $usuario['num_empleado'],
            'rol' => $rol['nombre'] ?? 'Sin rol',
            'permisos' => $listaPermisos,
            'foto' => $usuario['foto'],
            'isLoggedIn' => true,
        ]);

        // ✅ Redirigir correctamente
        return redirect()->to(base_url('dashboard'));
    }




    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('mensaje', 'Sesión cerrada correctamente.');
    }
}
