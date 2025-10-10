<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\CarreraModel;
use App\Models\CarreraGrupoModel;
use App\Models\UsuarioModel;

class GruposController extends BaseController
{
    protected $grupoModel;
    protected $carreraModel;
    protected $carreraGrupoModel;
    protected $usuarioModel;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->carreraModel = new CarreraModel();
        $this->carreraGrupoModel = new CarreraGrupoModel();
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        $data = [
            'grupos' => $this->carreraGrupoModel->obtenerGruposCompletos(),
            'carreras' => $this->carreraModel->where('activo', 1)->findAll(),
            'tutores' => $this->usuarioModel->where('rol_id', 3)->findAll(),

        ];

        return view('lms/admin/grupos/index', $data);
    }

    public function crear()
    {
        $carreraId = $this->request->getPost('carrera_id');
        $periodo = $this->request->getPost('periodo');
        $turno = $this->request->getPost('turno');
        $tutorId = $this->request->getPost('tutor_id');

        $carrera = $this->carreraModel->find($carreraId);
        if (!$carrera) {
            return redirect()->back()->with('msg', 'Carrera no encontrada');
        }

        $nombreGrupo = $this->grupoModel->generarNombre($carrera['siglas'], $periodo, $turno);

        $grupoId = $this->grupoModel->insert([
            'nombre' => $nombreGrupo,
            'periodo' => $periodo,
            'turno' => $turno,
            'activo' => 1,
        ]);

        $this->carreraGrupoModel->insert([
            'carrera_id' => $carreraId,
            'grupo_id' => $grupoId,
            'tutor_id' => $tutorId ?: null,
        ]);

        return redirect()->to(base_url('admin/grupos'))->with('msg', "Grupo <b>$nombreGrupo</b> creado correctamente");
    }

    public function eliminar($id)
    {
        $relacion = $this->carreraGrupoModel->find($id);
        if ($relacion) {
            $this->grupoModel->delete($relacion['grupo_id']);
            $this->carreraGrupoModel->delete($id);
        }
        return redirect()->back()->with('msg', 'Grupo eliminado correctamente');
    }
}
