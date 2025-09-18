<?php
namespace App\Controllers;

use App\Models\CarreraModel;
use App\Models\PlanModel;
use App\Models\BecaModel;

class Home extends BaseController
{
    public function index()
    {
        $carreraModel = new CarreraModel();
        $carreras = $carreraModel->findAll();

        $becaModel = new BecaModel();
        $becas = $becaModel->findAll();

        return view('home', [
            'title' => 'Inicio',
            'carreras' => $carreras,
            'becas' => $becas
        ]);
    }

    public function contacto()
    {
        return view('contacto', ['title' => 'Contacto']);
    }

    public function carrera($slug)
    {
        $carreraModel = new CarreraModel();
        $planModel = new PlanModel();

        $carrera = $carreraModel->where('slug', $slug)->first();

        if (!$carrera) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Carrera no encontrada");
        }

        // Traer materias del plan
        $materias = $planModel->getMateriasByCarreraSlug($slug);

        // Agrupar por ciclo
        $materiasPorCiclo = [];
        foreach ($materias as $materia) {
            $materiasPorCiclo[$materia['ciclo']][] = $materia;
        }

        return view('carrera_detalle', [
            'title' => $carrera['nombre'],
            'carrera' => $carrera,
            'materiasPorCiclo' => $materiasPorCiclo
        ]);
    }
}
