<?php

namespace App\Controllers\Alumno;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\GrupoMateriaProfesorModel;
use App\Models\PonderacionCicloModel;
use App\Models\MateriaGrupoAlumnoModel;
use App\Models\EntregaTareaModel;
use App\Models\EntregaProyectoModel;
use App\Models\ExamenRespuestaModel;

class CalificacionesController extends BaseController
{
    protected $usuarioModel;
    protected $asignacionModel;
    protected $ponderacionModel;
    protected $mgaModel;
    protected $tareaEntregaModel;
    protected $proyectoEntregaModel;
    protected $examenRespModel;

    protected $db;


    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->usuarioModel = new UsuarioModel();
        $this->asignacionModel = new GrupoMateriaProfesorModel();
        $this->ponderacionModel = new PonderacionCicloModel();
        $this->mgaModel = new MateriaGrupoAlumnoModel();
        $this->tareaEntregaModel = new EntregaTareaModel();
        $this->proyectoEntregaModel = new EntregaProyectoModel();
        $this->examenRespModel = new ExamenRespuestaModel();
    }

    /* =====================================================
       📌 Vista principal
    ====================================================== */
    public function index()
    {
        $AlumnoId = session('id');

        return view('lms/alumno/extras/calificaciones');
    }

}
