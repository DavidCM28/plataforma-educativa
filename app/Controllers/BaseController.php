<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

// 🔹 Importa tu modelo
use App\Models\CarreraLPModel;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * Carreras disponibles para navbar
     *
     * @var array
     */
    protected $carrerasNavbar = [];

    /**
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // 🔹 Cargar el modelo de carreras
        $carreraModel = new CarreraLPModel();
        $this->carrerasNavbar = $carreraModel->findAll();

        // 🔹 Pasar a todas las vistas automáticamente
        $renderer = service('renderer');
        $renderer->setVar('carrerasNavbar', $this->carrerasNavbar);
    }
}
