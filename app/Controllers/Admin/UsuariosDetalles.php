<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuarioDetalleModel;
use App\Models\UsuarioModel;

class UsuariosDetalles extends BaseController
{
    protected $usuarioModel;
    protected $detalleModel;

    public function index()
    {
        $usuarios = $this->usuarioModel
            ->select('usuarios.id, usuarios.nombre, usuarios.apellido_paterno, usuarios.apellido_materno, roles.nombre AS rol')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->orderBy('roles.nombre', 'ASC')
            ->orderBy('usuarios.nombre', 'ASC')
            ->findAll();

        // Agrupar por rol
        $grupos = [];
        foreach ($usuarios as $u) {
            $rol = ucfirst($u['rol'] ?? 'Sin Rol');
            $grupos[$rol][] = $u;
        }

        return view('lms/admin/usuarios_detalles/index', [
            'title' => 'Gestión de Datos Personales',
            'grupos' => $grupos
        ]);
    }


    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
        $this->detalleModel = new UsuarioDetalleModel();
    }

    /**
     * 📋 Muestra los detalles de un usuario
     */
    public function ver($id)
    {
        $usuario = $this->usuarioModel
            ->select('usuarios.*, roles.nombre AS rol')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->where('usuarios.id', $id)
            ->first();

        if (!$usuario) {
            return $this->response->setStatusCode(404)
                ->setJSON(['error' => true, 'message' => 'Usuario no encontrado']);
        }

        $detalles = $this->detalleModel->where('usuario_id', $id)->first();

        return $this->response->setJSON([
            'usuario' => $usuario,
            'detalles' => $detalles
        ]);
    }

    /**
     * 🧩 Guarda o actualiza los detalles de un usuario
     */
    public function guardar()
    {
        $idUsuario = $this->request->getPost('usuario_id');

        if (!$idUsuario) {
            return $this->response->setStatusCode(400)
                ->setJSON(['error' => true, 'message' => 'Falta el ID del usuario.']);
        }

        $data = [
            'usuario_id' => $idUsuario,

            // 📘 Datos personales
            'sexo' => $this->request->getPost('sexo'),
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento'),
            'estado_civil' => $this->request->getPost('estado_civil'),
            'curp' => strtoupper(trim($this->request->getPost('curp'))),
            'rfc' => strtoupper(trim($this->request->getPost('rfc'))),
            'pais_origen' => trim($this->request->getPost('pais_origen')),

            // ❤️ Datos médicos
            'peso' => $this->request->getPost('peso'),
            'estatura' => $this->request->getPost('estatura'),
            'tipo_sangre' => $this->request->getPost('tipo_sangre'),
            'antecedente_diabetico' => $this->request->getPost('antecedente_diabetico') ? 1 : 0,
            'antecedente_hipertenso' => $this->request->getPost('antecedente_hipertenso') ? 1 : 0,
            'antecedente_cardiaco' => $this->request->getPost('antecedente_cardiaco') ? 1 : 0,

            // 🏠 Domicilio
            'estado' => $this->request->getPost('estado'),
            'municipio' => $this->request->getPost('municipio'),
            'colonia' => $this->request->getPost('colonia'),
            'calle' => $this->request->getPost('calle'),
            'numero_exterior' => $this->request->getPost('numero_exterior'),
            'numero_interior' => $this->request->getPost('numero_interior'),

            // 📞 Comunicación
            'telefono' => $this->request->getPost('telefono'),
            'correo_alternativo' => $this->request->getPost('correo_alternativo'),
            'telefono_trabajo' => $this->request->getPost('telefono_trabajo'),

            // 🎓 Formación académica
            'grado_academico' => $this->request->getPost('grado_academico'),
            'descripcion_grado' => $this->request->getPost('descripcion_grado'),
            'cedula_profesional' => $this->request->getPost('cedula_profesional'),
        ];

        // 🔍 Verificar si ya tiene un registro
        $detalleExistente = $this->detalleModel->where('usuario_id', $idUsuario)->first();

        if ($detalleExistente) {
            $this->detalleModel->update($detalleExistente['id'], $data);
            $accion = 'actualizado';
        } else {
            $this->detalleModel->insert($data);
            $accion = 'registrado';
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Los detalles han sido $accion correctamente."
        ]);
    }

    public function buscarUsuario()
    {
        $term = $this->request->getGet('q');
        if (!$term || strlen($term) < 2) {
            return $this->response->setJSON([]);
        }

        $usuarios = $this->usuarioModel
            ->select('usuarios.id, usuarios.nombre, usuarios.apellido_paterno, usuarios.apellido_materno, roles.nombre as rol')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->groupStart()
            ->like('usuarios.nombre', $term)
            ->orLike('usuarios.apellido_paterno', $term)
            ->orLike('usuarios.apellido_materno', $term)
            ->groupEnd()
            ->orderBy('usuarios.nombre', 'ASC')
            ->limit(10)
            ->findAll();

        $results = [];

        foreach ($usuarios as $u) {
            $nombre = trim("{$u['nombre']} {$u['apellido_paterno']} {$u['apellido_materno']}");
            $results[] = [
                'id' => $u['id'],
                'nombre' => $nombre,
                'rol' => $u['rol'] ?? 'Sin rol'
            ];
        }

        return $this->response->setJSON(['results' => $results]);

    }


}
