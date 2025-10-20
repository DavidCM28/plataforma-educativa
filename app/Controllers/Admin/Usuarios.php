<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
use App\Models\CarreraModel;
use App\Models\AlumnoCarreraModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Usuarios extends BaseController
{
    public function index()
    {
        $usuarioModel = new UsuarioModel();
        $rolModel = new RolModel();
        $carreraModel = new CarreraModel();

        $usuarios = $usuarioModel
            ->select('usuarios.*, roles.nombre AS rol')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->findAll();

        $roles = $rolModel->findAll();
        $carreras = $carreraModel->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();

        return view('lms/admin/usuarios/index', [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'roles' => $roles,
            'carreras' => $carreras
        ]);
    }

    // ✅ Crear usuario y si es alumno, vincular a carrera + generar Excel de credenciales
    public function store()
    {
        $usuarioModel = new UsuarioModel();
        $rolModel = new RolModel();
        $alumnoCarreraModel = new AlumnoCarreraModel();
        $carreraModel = new CarreraModel();

        $rolId = $this->request->getPost('rol_id');
        $rol = $rolModel->find($rolId);
        $rolNombre = strtolower($rol['nombre']);

        $nombres = trim($this->request->getPost('nombres'));
        $apellidoPaterno = trim($this->request->getPost('apellido_paterno'));
        $apellidoMaterno = trim($this->request->getPost('apellido_materno'));
        $carreraId = $this->request->getPost('carrera_id');

        // 🔑 Contraseña temporal
        $passwordPlano = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
        $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // 🔢 Generar matrícula o número de empleado
        $matricula = null;
        $numEmpleado = null;

        if (strpos($rolNombre, 'alumno') !== false) {
            $ultimo = $usuarioModel->select('matricula')
                ->where('matricula IS NOT NULL')
                ->orderBy('CAST(matricula AS UNSIGNED) DESC')
                ->first();
            $nuevoNumero = $ultimo ? intval($ultimo['matricula']) + 1 : 1;
            $matricula = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
        } else {
            $ultimo = $usuarioModel->select('num_empleado')
                ->where('num_empleado IS NOT NULL')
                ->orderBy('CAST(num_empleado AS UNSIGNED) DESC')
                ->first();
            $nuevoNumero = $ultimo ? intval($ultimo['num_empleado']) + 1 : 1;
            $numEmpleado = str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
        }

        $email = ($matricula ?? $numEmpleado) . '@utmontemorelos.edu.mx';

        // 💾 Insertar usuario
        $usuarioId = $usuarioModel->insert([
            'nombre' => $nombres,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'email' => $email,
            'password' => $passwordHash,
            'rol_id' => $rolId,
            'matricula' => $matricula,
            'num_empleado' => $numEmpleado,
            'activo' => 1,
            'verificado' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        // 👨‍🎓 Si es alumno, vincular a carrera
        $carreraNombre = null;
        if (strpos($rolNombre, 'alumno') !== false && $carreraId) {
            $alumnoCarreraModel->insert([
                'alumno_id' => $usuarioId,
                'carrera_id' => $carreraId,
                'fecha_registro' => date('Y-m-d'),
                'estatus' => 'Activo'
            ]);
            $carrera = $carreraModel->find($carreraId);
            $carreraNombre = $carrera ? $carrera['nombre'] : null;
        }

        // 📄 Generar Excel con credenciales
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Credenciales');

        $headers = ['Nombre completo', 'Correo', 'Contraseña', 'Matrícula / Empleado', 'Rol', 'Carrera'];
        $sheet->fromArray([$headers], null, 'A1');

        $nombreCompleto = "{$nombres} {$apellidoPaterno} {$apellidoMaterno}";
        $sheet->fromArray([
            [
                $nombreCompleto,
                $email,
                $passwordPlano,
                $matricula ?? $numEmpleado,
                ucfirst($rolNombre),
                $carreraNombre
            ]
        ], null, 'A2');

        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'credenciales_' . date('Ymd_His') . '.xlsx';
        $tempPath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($tempPath);

        return $this->response->download($tempPath, null)->setFileName($filename);
    }

    // ✅ Importar usuarios desde Excel (alumnos y empleados)
    public function importar()
    {
        helper('text');
        $file = $this->request->getFile('archivo_excel');

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Debes seleccionar un archivo válido.');
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $rows = $sheet->rangeToArray("A2:D{$highestRow}", null, true, true, true);

        $usuarioModel = new UsuarioModel();
        $rolModel = new RolModel();
        $carreraModel = new CarreraModel();
        $alumnoCarreraModel = new AlumnoCarreraModel();

        $roles = $rolModel->findAll();
        $rolesMap = [];
        foreach ($roles as $r) {
            $rolesMap[strtolower(trim($r['nombre']))] = $r['id'];
        }

        $carreras = $carreraModel->findAll();
        $carrerasMap = [];
        foreach ($carreras as $c) {
            $carrerasMap[strtolower(trim($c['nombre']))] = $c['id'];
        }

        $ultimoAlumno = $usuarioModel->select('matricula')
            ->where('matricula IS NOT NULL')
            ->orderBy('CAST(matricula AS UNSIGNED) DESC')
            ->first();
        $contadorAlumno = $ultimoAlumno ? intval($ultimoAlumno['matricula']) : 0;

        $ultimoEmpleado = $usuarioModel->select('num_empleado')
            ->where('num_empleado IS NOT NULL')
            ->orderBy('CAST(num_empleado AS UNSIGNED) DESC')
            ->first();
        $contadorEmpleado = $ultimoEmpleado ? intval($ultimoEmpleado['num_empleado']) : 0;

        $report = [];

        foreach ($rows as $row) {
            $nombre = trim($row['A']);
            $apPat = trim($row['B']);
            $apMat = trim($row['C']);
            $campo4 = trim($row['D']); // puede ser rol o carrera

            if (!$nombre || !$apPat || !$campo4)
                continue;

            // Verificar si corresponde a alumno o empleado
            $esCarrera = isset($carrerasMap[strtolower($campo4)]);
            $isAlumno = $esCarrera;

            if ($isAlumno) {
                $rolId = $rolesMap['alumno'] ?? null;
                $carreraId = $carrerasMap[strtolower($campo4)] ?? null;
            } else {
                $rolId = $rolesMap[strtolower($campo4)] ?? null;
                $carreraId = null;
            }

            if (!$rolId)
                continue;

            $passwordPlano = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
            $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

            if ($isAlumno) {
                $contadorAlumno++;
                $codigo = str_pad($contadorAlumno, 6, '0', STR_PAD_LEFT);
                $matricula = $codigo;
                $numEmpleado = null;
            } else {
                $contadorEmpleado++;
                $codigo = str_pad($contadorEmpleado, 5, '0', STR_PAD_LEFT);
                $numEmpleado = $codigo;
                $matricula = null;
            }

            $email = ($matricula ?? $numEmpleado) . '@utmontemorelos.edu.mx';

            $data = [
                'nombre' => ucfirst($nombre),
                'apellido_paterno' => ucfirst($apPat),
                'apellido_materno' => ucfirst($apMat),
                'email' => $email,
                'password' => $passwordHash,
                'rol_id' => $rolId,
                'matricula' => $matricula,
                'num_empleado' => $numEmpleado,
                'activo' => 1,
                'verificado' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $id = $usuarioModel->insert($data);

            if ($isAlumno && $carreraId) {
                $alumnoCarreraModel->insert([
                    'alumno_id' => $id,
                    'carrera_id' => $carreraId,
                    'fecha_registro' => date('Y-m-d'),
                    'estatus' => 'Activo'
                ]);
            }

            $report[] = [
                'nombre' => "{$nombre} {$apPat} {$apMat}",
                'correo' => $email,
                'password' => $passwordPlano,
                'codigo' => $codigo,
                'rol' => $isAlumno ? 'Alumno' : ucfirst($campo4),
                'carrera' => $isAlumno ? $campo4 : '-'
            ];
        }

        // 🔹 Generar Excel con credenciales importadas
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Credenciales');

        $headers = ['Nombre', 'Rol', 'Código', 'Correo', 'Contraseña', 'Carrera'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        $row = 2;
        foreach ($report as $r) {
            $sheet->fromArray([
                [
                    $r['nombre'],
                    $r['rol'],
                    $r['codigo'],
                    $r['correo'],
                    $r['password'],
                    $r['carrera']
                ]
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'credenciales_importadas_' . date('Ymd_His') . '.xlsx';
        $tempPath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($tempPath);

        return $this->response->download($tempPath, null)->setFileName($filename);
    }

    // ✅ Listado simple de carreras (para autocompletar / AJAX)
    public function obtenerCarreras()
    {
        $carreras = (new CarreraModel())
            ->select('id, nombre')
            ->where('activo', 1)
            ->orderBy('nombre', 'ASC')
            ->findAll();

        return $this->response->setJSON($carreras);
    }

    // =======================================================
// 📄 DESCARGA PLANTILLA DE ALUMNOS
// =======================================================
    public function plantillaAlumnos()
    {
        $carreraModel = new CarreraModel();
        $rolModel = new RolModel();

        // 🔹 Obtener el rol de alumno
        $rolAlumno = $rolModel->where('LOWER(nombre)', 'alumno')->first();

        // 🔹 Obtener todas las carreras activas
        $carreras = $carreraModel->select('nombre')->where('activo', 1)->orderBy('nombre', 'ASC')->findAll();

        $spreadsheet = new Spreadsheet();

        // 🧾 Hoja principal
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Alumnos');
        $headers = ['Nombres', 'Apellido paterno', 'Apellido materno', 'Carrera', 'Rol'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        // Ejemplo de fila guía
        $sheet->fromArray(
            [['Juan', 'Pérez', 'López', $carreras[0]['nombre'] ?? 'Ejemplo Carrera', $rolAlumno['nombre'] ?? 'Alumno']],
            null,
            'A2'
        );

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🧭 Crear hoja oculta de catálogos
        $catalogSheet = $spreadsheet->createSheet();
        $catalogSheet->setTitle('Catálogos');

        $catalogSheet->setCellValue('A1', 'Carreras');
        $row = 2;
        foreach ($carreras as $c) {
            $catalogSheet->setCellValue("A{$row}", $c['nombre']);
            $row++;
        }

        $catalogSheet->setCellValue('C1', 'Roles');
        $catalogSheet->setCellValue('C2', $rolAlumno['nombre'] ?? 'Alumno');

        // 🧩 Validación para columna "Carrera"
        $validationCarrera = $sheet->getCell('D2')->getDataValidation();
        $validationCarrera->setType(DataValidation::TYPE_LIST);
        $validationCarrera->setErrorStyle(DataValidation::STYLE_STOP);
        $validationCarrera->setAllowBlank(false);
        $validationCarrera->setShowDropDown(true);
        $validationCarrera->setFormula1('=Catálogos!$A$2:$A$' . ($row - 1));

        // 🧩 Validación para columna "Rol"
        $validationRol = $sheet->getCell('E2')->getDataValidation();
        $validationRol->setType(DataValidation::TYPE_LIST);
        $validationRol->setErrorStyle(DataValidation::STYLE_STOP);
        $validationRol->setAllowBlank(false);
        $validationRol->setShowDropDown(true);
        $validationRol->setFormula1('=Catálogos!$C$2:$C$2');

        // Ocultar hoja de catálogos
        $catalogSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // 📤 Exportar Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_alumnos_' . date('Ymd_His') . '.xlsx';
        $tempPath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($tempPath);

        return $this->response->download($tempPath, null)->setFileName($filename);
    }



    // =======================================================
// 📄 DESCARGA PLANTILLA DE EMPLEADOS (profesores y escolares)
// =======================================================
    public function plantillaEmpleados()
    {
        $rolModel = new RolModel();

        // 🔹 Obtener roles relevantes
        $roles = $rolModel
            ->select('nombre')
            ->whereIn('LOWER(nombre)', ['profesor', 'escolares', 'administrativo'])
            ->orderBy('nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();

        // 🧾 Hoja principal
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Empleados');
        $headers = ['Nombres', 'Apellido paterno', 'Apellido materno', 'Rol'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        // Ejemplo de fila guía
        $sheet->fromArray(
            [['María', 'García', 'Reyes', $roles[0]['nombre'] ?? 'Profesor']],
            null,
            'A2'
        );

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🧭 Crear hoja oculta de roles
        $catalogSheet = $spreadsheet->createSheet();
        $catalogSheet->setTitle('Catálogos');
        $catalogSheet->setCellValue('A1', 'Roles');

        $row = 2;
        foreach ($roles as $r) {
            $catalogSheet->setCellValue("A{$row}", $r['nombre']);
            $row++;
        }

        // 🧩 Validación para columna "Rol"
        $validation = $sheet->getCell('D2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setFormula1('=Catálogos!$A$2:$A$' . ($row - 1));

        // Ocultar hoja de catálogos
        $catalogSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // 📤 Exportar Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'plantilla_empleados_' . date('Ymd_His') . '.xlsx';
        $tempPath = WRITEPATH . 'uploads/' . $filename;
        $writer->save($tempPath);

        return $this->response->download($tempPath, null)->setFileName($filename);
    }

    public function detalle($id)
    {
        $usuarioModel = new UsuarioModel();

        $usuario = $usuarioModel
            ->select('
            usuarios.*,
            roles.nombre AS rol,
            carreras.nombre AS carrera
        ')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->join('alumno_carrera', 'alumno_carrera.alumno_id = usuarios.id', 'left')
            ->join('carreras', 'carreras.id = alumno_carrera.carrera_id', 'left')
            ->where('usuarios.id', $id)
            ->first();

        if (!$usuario) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'Usuario no encontrado']);
        }

        return $this->response->setJSON($usuario);
    }

    // =======================================================
// 🗑️ ELIMINAR USUARIO
// =======================================================
    public function delete($id)
    {
        $usuarioModel = new UsuarioModel();
        $alumnoCarreraModel = new AlumnoCarreraModel();

        // Verificar si el usuario existe
        $usuario = $usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ]);
        }

        // Eliminar relación en alumno_carrera si existe
        $alumnoCarreraModel->where('alumno_id', $id)->delete();

        // Eliminar usuario
        $usuarioModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario eliminado correctamente'
        ]);
    }


}

