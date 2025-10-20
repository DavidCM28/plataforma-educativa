<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GrupoModel;
use App\Models\UsuarioModel;
use App\Models\GrupoAlumnoModel;
use App\Models\CarreraGrupoModel;
use App\Models\AlumnoCarreraModel;
use App\Models\GrupoMateriaProfesorModel;

class AsignacionesAlumnosController extends BaseController
{
    protected $grupoModel, $usuarioModel, $grupoAlumnoModel,
    $carreraGrupoModel, $alumnoCarreraModel, $grupoMateriaProfesorModel, $db;

    public function __construct()
    {
        $this->grupoModel = new GrupoModel();
        $this->usuarioModel = new UsuarioModel();
        $this->grupoAlumnoModel = new GrupoAlumnoModel();
        $this->carreraGrupoModel = new CarreraGrupoModel();
        $this->alumnoCarreraModel = new AlumnoCarreraModel();
        $this->grupoMateriaProfesorModel = new GrupoMateriaProfesorModel();
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        // 🔹 Grupos originales (sin filtro)
        $todosLosGrupos = $this->carreraGrupoModel->obtenerGruposCompletos();

        // 🔹 Grupos del primer ciclo (solo para alumnos)
        $gruposPrimerCiclo = array_filter($todosLosGrupos, function ($g) {
            // busca un "1" dentro del nombre del grupo, como IS1M, ADM1V, etc.
            return preg_match('/\b1\b|1M|1V|1A/i', $g['grupo']);
        });

        $alumnos = $this->usuarioModel
            ->select('id, nombre')
            ->where('rol_id', 4)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        $carreras = $this->db->table('carreras')
            ->select('id, nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->get()
            ->getResultArray();

        $vinculos = $this->alumnoCarreraModel
            ->select('alumno_carrera.id, usuarios.nombre AS alumno, carreras.nombre AS carrera, alumno_carrera.estatus')
            ->join('usuarios', 'usuarios.id = alumno_carrera.alumno_id')
            ->join('carreras', 'carreras.id = alumno_carrera.carrera_id')
            ->orderBy('usuarios.nombre', 'ASC')
            ->findAll();

        $inscripciones = $this->grupoAlumnoModel
            ->select('grupo_alumno.*, grupos.nombre as grupo, usuarios.nombre as alumno')
            ->join('grupos', 'grupos.id = grupo_alumno.grupo_id')
            ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
            ->findAll();

        // 🔹 Enviamos ambas versiones (una para cada parte del tab)
        return view('lms/admin/asignaciones/alumnos', [
            'grupos' => $gruposPrimerCiclo, // esta la usará el subtab de alumnos
            'todosLosGrupos' => $todosLosGrupos, // por si luego la usas en profesores
            'alumnos' => $alumnos,
            'carreras' => $carreras,
            'vinculos' => $vinculos,
            'inscripciones' => $inscripciones
        ]);
    }


    public function vincularAlumnoCarrera()
    {
        $alumnoId = $this->request->getPost('alumno_id');
        $carreraId = $this->request->getPost('carrera_id');

        if (!$alumnoId || !$carreraId) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Faltan datos.']);
        }

        $existe = $this->alumnoCarreraModel
            ->where('alumno_id', $alumnoId)
            ->where('carrera_id', $carreraId)
            ->first();

        if ($existe) {
            return $this->response->setJSON(['ok' => false, 'msg' => '⚠️ El alumno ya está vinculado a esa carrera.']);
        }

        $this->alumnoCarreraModel->insert([
            'alumno_id' => $alumnoId,
            'carrera_id' => $carreraId,
            'fecha_registro' => date('Y-m-d'),
            'estatus' => 'Activo',
        ]);

        return $this->response->setJSON(['ok' => true, 'msg' => '✅ Alumno vinculado a la carrera.']);
    }

    public function asignarAlumno()
    {
        $grupoId = $this->request->getPost('grupo_id');
        $alumnosSeleccionados = $this->request->getPost('alumnos') ?? [$this->request->getPost('alumno_id')];

        if (!$grupoId || empty($alumnosSeleccionados)) {
            return redirect()->back()->with('msg', '⚠️ Debes seleccionar un grupo y al menos un alumno.');
        }

        $relacion = $this->carreraGrupoModel
            ->select('carrera_grupo.carrera_id')
            ->where('carrera_grupo.grupo_id', $grupoId)
            ->first();

        if (!$relacion) {
            return redirect()->back()->with('msg', '❌ El grupo no está vinculado a ninguna carrera.');
        }

        $carreraId = $relacion['carrera_id'];
        $grupo = $this->grupoModel->find($grupoId);
        $limite = $grupo['limite'] ?? 0;
        $actuales = $this->grupoAlumnoModel->where('grupo_id', $grupoId)->countAllResults();

        if ($limite && $actuales >= $limite) {
            return redirect()->back()->with('msg', '⚠️ El grupo ya alcanzó su límite de alumnos.');
        }

        $alumnosValidos = $this->alumnoCarreraModel
            ->select('alumno_id')
            ->where('carrera_id', $carreraId)
            ->whereIn('alumno_id', $alumnosSeleccionados)
            ->findAll();

        if (empty($alumnosValidos)) {
            return redirect()->back()->with('msg', '⚠️ Ninguno de los alumnos seleccionados pertenece a la carrera del grupo.');
        }

        foreach ($alumnosValidos as $a) {
            $alumnoId = $a['alumno_id'];
            $yaInscrito = $this->grupoAlumnoModel
                ->where('grupo_id', $grupoId)
                ->where('alumno_id', $alumnoId)
                ->first();

            if ($yaInscrito)
                continue;

            $this->grupoAlumnoModel->insert([
                'grupo_id' => $grupoId,
                'alumno_id' => $alumnoId,
                'fecha_inscripcion' => date('Y-m-d'),
                'estatus' => 'Inscrito',
            ]);

            $grupoAlumnoId = $this->grupoAlumnoModel->getInsertID();

            $asignaciones = $this->grupoMateriaProfesorModel->where('grupo_id', $grupoId)->findAll();
            foreach ($asignaciones as $asig) {
                $this->db->table('materia_grupo_alumno')->insert([
                    'grupo_materia_profesor_id' => $asig['id'],
                    'grupo_alumno_id' => $grupoAlumnoId,
                    'calificacion_final' => null,
                    'asistencia' => 0,
                ]);
            }
        }

        return redirect()->back()->with('msg', '✅ Alumnos asignados correctamente al grupo y materias.');
    }

    public function eliminarAlumno($id)
    {
        $this->grupoAlumnoModel->delete($id);
        return $this->response->setJSON(['ok' => true, 'msg' => 'Alumno eliminado correctamente.']);
    }

    public function alumnosPorCarrera($grupoId)
    {
        $relacion = $this->carreraGrupoModel
            ->select('carrera_grupo.carrera_id')
            ->where('carrera_grupo.grupo_id', $grupoId)
            ->first();

        if (!$relacion) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '❌ El grupo no está vinculado a ninguna carrera.'
            ]);
        }

        $carreraId = $relacion['carrera_id'];

        // 🔹 Alumnos de la carrera que aún NO están inscritos en ningún grupo
        $alumnos = $this->usuarioModel
            ->select('usuarios.id, usuarios.nombre')
            ->join('alumno_carrera', 'alumno_carrera.alumno_id = usuarios.id')
            ->where('alumno_carrera.carrera_id', $carreraId)
            ->where('usuarios.rol_id', 4)
            ->where('alumno_carrera.estatus', 'Activo')
            ->whereNotIn('usuarios.id', function ($builder) {
                return $builder->select('alumno_id')->from('grupo_alumno');
            })
            ->orderBy('usuarios.nombre', 'ASC')
            ->findAll();

        if (empty($alumnos)) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '⚠️ No hay alumnos disponibles sin grupo en esta carrera.'
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'alumnos' => $alumnos
        ]);
    }


    public function buscarAlumno()
    {
        $termino = $this->request->getGet('q');

        if (!$termino) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Sin término de búsqueda']);
        }

        $builder = $this->db->table('usuarios u');
        $builder->select("
        u.id,
        CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
        u.matricula,
        c.nombre AS carrera
    ");
        $builder->join('alumno_carrera ac', 'ac.alumno_id = u.id', 'left');
        $builder->join('carreras c', 'c.id = ac.carrera_id', 'left');
        $builder->where('u.rol_id', 4);
        $builder->groupStart()
            ->like("CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno)", $termino)
            ->orLike('u.matricula', $termino)
            ->groupEnd();
        $builder->orderBy('u.nombre', 'ASC');
        $builder->limit(10);

        $resultados = $builder->get()->getResultArray();

        return $this->response->setJSON(['ok' => true, 'alumnos' => $resultados]);
    }

    /**
     * Promueve a los alumnos de un grupo al siguiente ciclo, creando un nuevo grupo.
     */
    public function promoverGrupo($grupoId)
    {
        $grupoActual = $this->grupoModel->find($grupoId);

        if (!$grupoActual) {
            return $this->response->setJSON(['ok' => false, 'msg' => '❌ Grupo no encontrado.']);
        }

        // Buscar la relación carrera-grupo
        $relacion = $this->carreraGrupoModel
            ->where('grupo_id', $grupoId)
            ->first();

        if (!$relacion) {
            return $this->response->setJSON(['ok' => false, 'msg' => '⚠️ Este grupo no está vinculado a una carrera.']);
        }

        // Calcular el nuevo ciclo (ej. de 3 → 4)
        $nombreGrupo = $grupoActual['nombre'];
        preg_match('/(\d+)/', $nombreGrupo, $matches);
        $numeroActual = $matches[1] ?? null;
        $nuevoCiclo = $numeroActual ? ((int) $numeroActual + 1) : null;

        if (!$nuevoCiclo) {
            return $this->response->setJSON(['ok' => false, 'msg' => '⚠️ No se pudo detectar el número de ciclo actual.']);
        }

        // Crear nuevo grupo
        $nuevoGrupo = [
            'nombre' => preg_replace('/\d+/', $nuevoCiclo, $nombreGrupo, 1),
            'periodo' => $grupoActual['periodo'],
            'turno' => $grupoActual['turno'],
            'activo' => 1,
        ];

        $this->grupoModel->insert($nuevoGrupo);
        $nuevoGrupoId = $this->grupoModel->getInsertID();

        // Vincular el nuevo grupo con la misma carrera
        $this->carreraGrupoModel->insert([
            'carrera_id' => $relacion['carrera_id'],
            'grupo_id' => $nuevoGrupoId,
        ]);

        // Obtener alumnos activos del grupo actual
        $alumnosActuales = $this->grupoAlumnoModel
            ->where('grupo_id', $grupoId)
            ->where('estatus', 'Inscrito')
            ->findAll();

        // Pasarlos al nuevo grupo
        foreach ($alumnosActuales as $a) {
            $this->grupoAlumnoModel->insert([
                'grupo_id' => $nuevoGrupoId,
                'alumno_id' => $a['alumno_id'],
                'fecha_inscripcion' => date('Y-m-d'),
                'estatus' => 'Inscrito',
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'msg' => '✅ Se creó el grupo del siguiente ciclo y se promovieron los alumnos.'
        ]);
    }

    public function alumnosInscritos($grupoId)
    {
        $alumnos = $this->grupoAlumnoModel
            ->select('
    grupo_alumno.id,
    usuarios.matricula,
    CONCAT(usuarios.nombre, " ", usuarios.apellido_paterno, " ", usuarios.apellido_materno) AS alumno,
    grupo_alumno.estatus
')

            ->join('usuarios', 'usuarios.id = grupo_alumno.alumno_id')
            ->where('grupo_alumno.grupo_id', $grupoId)
            ->orderBy('usuarios.nombre', 'ASC')
            ->findAll();

        if (empty($alumnos)) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => 'No hay alumnos inscritos en este grupo.'
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'alumnos' => $alumnos
        ]);
    }

    public function gruposPorCarrera($carreraId)
    {
        $grupos = $this->carreraGrupoModel
            ->select('grupos.id, grupos.nombre AS grupo')
            ->join('grupos', 'grupos.id = carrera_grupo.grupo_id')
            ->where('carrera_grupo.carrera_id', $carreraId)
            ->where('grupos.activo', 1)
            ->orderBy('grupos.nombre', 'ASC')
            ->get()
            ->getResultArray();

        if (empty($grupos)) {
            return $this->response->setJSON([
                'ok' => false,
                'msg' => '⚠️ No hay grupos registrados para esta carrera.'
            ]);
        }

        return $this->response->setJSON(['ok' => true, 'grupos' => $grupos]);
    }


}
