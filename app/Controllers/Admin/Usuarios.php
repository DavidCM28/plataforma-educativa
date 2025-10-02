<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;
use App\Models\RolModel;
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

        $usuarios = $usuarioModel
            ->select('usuarios.*, roles.nombre AS rol')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->findAll();

        $roles = $rolModel->findAll();

        return view('lms/admin/usuarios/index', [
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuarios,
            'roles' => $roles // 👈 para llenar el select del modal
        ]);
    }


    public function create()
    {
        $roles = (new RolModel())->findAll();

        return view('lms/admin/usuarios/create', [
            'title' => 'Nuevo Usuario',
            'roles' => $roles
        ]);
    }

    public function store()
    {
        $model = new UsuarioModel();
        $rolModel = new RolModel();

        $rolId = $this->request->getPost('rol_id');
        $rol = $rolModel->find($rolId);
        $rolNombre = strtolower($rol['nombre']);

        // === Datos base ===
        $nombres = trim($this->request->getPost('nombres'));
        $apellidoPaterno = trim($this->request->getPost('apellido_paterno'));
        $apellidoMaterno = trim($this->request->getPost('apellido_materno'));

        // === Generar contraseña temporal ===
        $passwordPlano = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
        $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // === Generar matrícula o número de empleado ===
        $matricula = null;
        $numEmpleado = null;

        if (strpos($rolNombre, 'alumno') !== false) {
            // Buscar la última matrícula usada
            $ultimo = $model->select('matricula')
                ->where('matricula IS NOT NULL')
                ->orderBy('matricula', 'DESC')
                ->first();

            $ultimoNumero = $ultimo ? intval($ultimo['matricula']) : 0;
            $nuevoNumero = $ultimoNumero + 1;
            $matricula = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);

        } else {
            // Buscar el último número de empleado
            $ultimo = $model->select('num_empleado')
                ->where('num_empleado IS NOT NULL')
                ->orderBy('num_empleado', 'DESC')
                ->first();

            $ultimoNumero = $ultimo ? intval($ultimo['num_empleado']) : 0;
            $nuevoNumero = $ultimoNumero + 1;
            $numEmpleado = str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
        }

        // === Generar correo institucional ===
        $dominio = "@utmontemorelos.edu.mx";
        $email = ($matricula ?? $numEmpleado) . $dominio;

        // === Guardar ===
        $data = [
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
        ];

        $model->insert($data);

        return $this->response->setJSON([
            'success' => true,
            'email' => $email,
            'password' => $passwordPlano,
            'matricula' => $matricula,
            'num_empleado' => $numEmpleado
        ]);
    }

    public function update($id)
    {
        $model = new UsuarioModel();
        $data = $this->request->getPost();

        // Quita campos vacíos
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $model->update($id, $data);

        return $this->response->setJSON(['success' => true]);
    }


    public function delete($id)
    {
        $model = new UsuarioModel();

        // Verificar si el usuario existe antes de eliminar
        $usuario = $model->find($id);
        if (!$usuario) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ]);
        }

        // Eliminar usuario
        $model->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario eliminado correctamente.'
        ]);
    }


    public function detalle($id)
    {
        $model = new UsuarioModel();

        // Buscar usuario con su rol e ID del rol
        $usuario = $model
            ->select('usuarios.*, roles.nombre AS rol, roles.id AS rol_id')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->where('usuarios.id', $id)
            ->first();

        // Si no se encuentra el usuario, devolver error 404 JSON
        if (!$usuario) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => true,
                'message' => 'Usuario no encontrado'
            ]);
        }

        // Devolver el usuario en formato JSON
        return $this->response->setJSON([
            'id' => $usuario['id'],
            'nombre' => $usuario['nombre'],
            'apellido_paterno' => $usuario['apellido_paterno'] ?? '',
            'apellido_materno' => $usuario['apellido_materno'] ?? '',
            'email' => $usuario['email'],
            'matricula' => $usuario['matricula'],
            'num_empleado' => $usuario['num_empleado'],
            'rol' => $usuario['rol'],
            'rol_id' => $usuario['rol_id'], // 👈 agregado
            'activo' => $usuario['activo'],
            'verificado' => $usuario['verificado'],
            'ultimo_login' => $usuario['ultimo_login'],
            'created_at' => $usuario['created_at'],
            'updated_at' => $usuario['updated_at'],
            'deleted_at' => $usuario['deleted_at'],
            'foto' => $usuario['foto'] ?? null
        ]);
    }

    public function plantilla()
    {
        $roles = (new RolModel())
            ->select('nombre')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();

        // Hoja principal
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuarios');

        // Encabezados (solo campos del formulario)
        $headers = ['nombres', 'apellido_paterno', 'apellido_materno', 'rol'];
        $sheet->fromArray([$headers], null, 'A1');

        // Estilos sencillos de encabezado
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);
        $sheet->getStyle('A1:D1')->getFill()->setFillType('solid')->getStartColor()->setRGB('FFF2CC');
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->freezePane('A2');

        // Hoja de catálogos (roles)
        $catalog = $spreadsheet->createSheet();
        $catalog->setTitle('Catálogos');
        $catalog->setCellValue('A1', 'Roles');

        $row = 2;
        foreach ($roles as $r) {
            $catalog->setCellValue("A{$row}", $r['nombre']);
            $row++;
        }

        // (Opcional) Ocultar hoja de catálogos
        $spreadsheet->getSheetByName('Catálogos')->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // Validación de datos para la columna Rol (D)
        // Aplica la lista A2:A{row-1} de la hoja Catálogos
        $lastRoleRow = $row - 1;
        $validationRange = "'Catálogos'!A2:A{$lastRoleRow}";

        // Aplica validación a un rango amplio (por ejemplo D2:D1000)
        for ($i = 2; $i <= 1000; $i++) {
            $validation = $sheet->getCell("D{$i}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Valor inválido');
            $validation->setError('Selecciona un rol de la lista.');
            $validation->setPromptTitle('Rol');
            $validation->setPrompt('Elige un rol de la lista desplegable.');
            $validation->setFormula1($validationRange);
        }

        // Nota guía en A2 (opcional, puedes borrar esta fila al importar)
        $sheet->setCellValue('A2', 'Ej.: Juan');
        $sheet->setCellValue('B2', 'Ej.: Pérez');
        $sheet->setCellValue('C2', 'Ej.: Gómez');
        $sheet->setCellValue('D2', $roles[0]['nombre'] ?? 'Alumno');

        // Descargar
        $writer = new Xlsx($spreadsheet);
        // En CI4, conviene buffer para enviar el binario
        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_usuarios_formulario.xlsx"')
            ->setBody($excelOutput);
    }

    public function importar()
    {
        helper('text'); // para random_string()

        $file = $this->request->getFile('archivo_excel');
        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'Debes seleccionar un archivo válido.');
        }

        // Cargar Excel
        $spreadsheet = IOFactory::load($file->getTempName());
        $sheet = $spreadsheet->getActiveSheet();

        // === Detectar automáticamente última fila con datos reales ===
        $highestRow = $sheet->getHighestRow();
        $lastDataRow = 1;

        for ($i = $highestRow; $i >= 2; $i--) { // empieza desde el final hacia arriba
            $nombre = trim($sheet->getCell("A{$i}")->getValue());
            $apPat = trim($sheet->getCell("B{$i}")->getValue());
            $rol = trim($sheet->getCell("D{$i}")->getValue());

            if ($nombre !== '' || $apPat !== '' || $rol !== '') {
                $lastDataRow = $i;
                break;
            }
        }

        // Leer solo filas con datos reales
        $rows = $sheet->rangeToArray("A2:D{$lastDataRow}", null, true, true, true);

        $usuarioModel = new UsuarioModel();
        $rolModel = new RolModel();

        $roles = $rolModel->findAll();
        $rolesMap = [];
        foreach ($roles as $r) {
            $rolesMap[strtolower(trim($r['nombre']))] = $r['id'];
        }

        // === Inicializar contadores ===
        $ultimoAlumno = $usuarioModel->select('matricula')
            ->where('matricula IS NOT NULL')
            ->orderBy('matricula', 'DESC')
            ->first();
        $contadorAlumno = $ultimoAlumno ? intval($ultimoAlumno['matricula']) : 0;

        $ultimoEmpleado = $usuarioModel->select('num_empleado')
            ->where('num_empleado IS NOT NULL')
            ->orderBy('num_empleado', 'DESC')
            ->first();
        $contadorEmpleado = $ultimoEmpleado ? intval($ultimoEmpleado['num_empleado']) : 0;

        $importados = 0;
        $errores = [];
        $report = [];

        foreach ($rows as $index => $row) {
            $nombre = trim($row['A'] ?? '');
            $apPat = trim($row['B'] ?? '');
            $apMat = trim($row['C'] ?? '');
            $rolNombre = strtolower(trim($row['D'] ?? ''));

            // Saltar si está vacío
            if ($nombre === '' && $apPat === '' && $rolNombre === '')
                continue;

            if (empty($nombre) || empty($apPat) || empty($rolNombre)) {
                $errores[] = "Fila " . ($index + 2) . ": campos incompletos.";
                continue;
            }

            if (!isset($rolesMap[$rolNombre])) {
                $errores[] = "Fila " . ($index + 2) . ": rol '{$rolNombre}' no válido.";
                continue;
            }

            $rol_id = $rolesMap[$rolNombre];
            $isAlumno = strpos($rolNombre, 'alumno') !== false;

            // === Generar contraseña temporal ===
            $passwordPlano = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
            $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

            // === Generar matrícula / num_empleado incremental ===
            if ($isAlumno) {
                $contadorAlumno++;
                $codigo = str_pad($contadorAlumno, 6, '0', STR_PAD_LEFT);
                $matricula = $codigo;
                $numEmpleado = null;
            } else {
                $contadorEmpleado++;
                $codigo = str_pad($contadorEmpleado, 5, '0', STR_PAD_LEFT);
                $matricula = null;
                $numEmpleado = $codigo;
            }

            // === Correo institucional ===
            $dominio = "@utmontemorelos.edu.mx";
            $email = ($matricula ?? $numEmpleado) . $dominio;

            // === Datos finales ===
            $data = [
                'nombre' => ucfirst($nombre),
                'apellido_paterno' => ucfirst($apPat),
                'apellido_materno' => ucfirst($apMat),
                'email' => $email,
                'password' => $passwordHash,
                'rol_id' => $rol_id,
                'matricula' => $matricula,
                'num_empleado' => $numEmpleado,
                'activo' => 1,
                'verificado' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ];

            try {
                $usuarioModel->insert($data);
                $importados++;

                // Agregar a reporte
                $report[] = [
                    'nombre' => "{$nombre} {$apPat}",
                    'rol' => ucfirst($rolNombre),
                    'codigo' => $codigo,
                    'email' => $email,
                    'password' => $passwordPlano,
                ];

            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 2) . ": error al insertar ({$e->getMessage()}).";
            }
        }

        // === Guardar reporte ===
        session()->setFlashdata('import_report', json_encode($report));

        $msg = "{$importados} usuarios importados correctamente.";
        if (!empty($errores)) {
            $msg .= "<br>Algunos registros fallaron:<br>" . implode('<br>', $errores);
        }

        return redirect()->back()->with('success', $msg);
    }



    public function descargarCredenciales()
    {
        $reporte = session()->getFlashdata('import_report');

        if (!$reporte) {
            return redirect()->back()->with('error', 'No hay credenciales disponibles.');
        }

        $datos = json_decode($reporte, true);
        $filename = 'credenciales_generadas_' . date('Ymd_His') . '.csv';

        $f = fopen('php://memory', 'w');
        fputcsv($f, ['Nombre', 'Rol', 'Código', 'Correo', 'Contraseña'], ',');

        foreach ($datos as $d) {
            fputcsv($f, [$d['nombre'], $d['rol'], $d['codigo'], $d['email'], $d['password']], ',');
        }

        fseek($f, 0);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);
        exit;
    }

}
