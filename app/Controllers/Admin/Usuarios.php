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

        $nombres = trim($this->request->getPost('nombres'));
        $apellidoPaterno = trim($this->request->getPost('apellido_paterno'));
        $apellidoMaterno = trim($this->request->getPost('apellido_materno'));

        // Generar contraseña temporal
        $passwordPlano = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789'), 0, 8);
        $passwordHash = password_hash($passwordPlano, PASSWORD_BCRYPT);

        // Generar matrícula o número de empleado (buscando el más alto existente)
        $matricula = null;
        $numEmpleado = null;

        if (strpos($rolNombre, 'alumno') !== false) {
            $ultimo = $model->select('matricula')
                ->where('matricula IS NOT NULL')
                ->orderBy('CAST(matricula AS UNSIGNED) DESC')
                ->first();

            $ultimoNumero = $ultimo ? intval($ultimo['matricula']) : 0;
            $nuevoNumero = $ultimoNumero + 1;
            $matricula = str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
        } else {
            $ultimo = $model->select('num_empleado')
                ->where('num_empleado IS NOT NULL')
                ->orderBy('CAST(num_empleado AS UNSIGNED) DESC')
                ->first();

            $ultimoNumero = $ultimo ? intval($ultimo['num_empleado']) : 0;
            $nuevoNumero = $ultimoNumero + 1;
            $numEmpleado = str_pad($nuevoNumero, 5, '0', STR_PAD_LEFT);
        }


        $dominio = "@utmontemorelos.edu.mx";
        $email = ($matricula ?? $numEmpleado) . $dominio;

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
        $usuario = $model->find($id);

        if (!$usuario) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Usuario no encontrado.'
            ]);
        }

        // 🔥 Eliminación física
        $model->where('id', $id)->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Usuario eliminado definitivamente.'
        ]);
    }

    public function detalle($id)
    {
        $model = new UsuarioModel();

        $usuario = $model
            ->select('usuarios.*, roles.nombre AS rol, roles.id AS rol_id')
            ->join('roles', 'roles.id = usuarios.rol_id', 'left')
            ->where('usuarios.id', $id)
            ->first();

        if (!$usuario) {
            return $this->response->setStatusCode(404)->setJSON([
                'error' => true,
                'message' => 'Usuario no encontrado'
            ]);
        }

        return $this->response->setJSON($usuario);
    }

    // 📥 Plantilla Excel
    public function plantilla()
    {
        $roles = (new RolModel())
            ->select('nombre')
            ->orderBy('nombre', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Usuarios');
        $headers = ['nombres', 'apellido_paterno', 'apellido_materno', 'rol'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:D1')->getFont()->setBold(true);

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $catalog = $spreadsheet->createSheet();
        $catalog->setTitle('Catálogos');
        $catalog->setCellValue('A1', 'Roles');
        $r = 2;
        foreach ($roles as $rol) {
            $catalog->setCellValue("A{$r}", $rol['nombre']);
            $r++;
        }

        $spreadsheet->getSheetByName('Catálogos')
            ->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excel = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="plantilla_usuarios.xlsx"')
            ->setBody($excel);
    }

    // 📤 Importar Excel
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
        $roles = $rolModel->findAll();

        $rolesMap = [];
        foreach ($roles as $r) {
            $rolesMap[strtolower(trim($r['nombre']))] = $r['id'];
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
        $errores = [];
        $importados = 0;

        foreach ($rows as $index => $row) {
            $nombre = trim($row['A']);
            $apPat = trim($row['B']);
            $apMat = trim($row['C']);
            $rolNombre = strtolower(trim($row['D']));

            if (!$nombre || !$apPat || !$rolNombre)
                continue;
            if (!isset($rolesMap[$rolNombre]))
                continue;

            $rolId = $rolesMap[$rolNombre];
            $isAlumno = strpos($rolNombre, 'alumno') !== false;

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
                $matricula = null;
                $numEmpleado = $codigo;
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

            try {
                $usuarioModel->insert($data);
                $importados++;

                $report[] = [
                    'nombre' => "{$nombre} {$apPat}",
                    'rol' => ucfirst($rolNombre),
                    'codigo' => $codigo,
                    'email' => $email,
                    'password' => $passwordPlano
                ];
            } catch (\Exception $e) {
                $errores[] = "Fila " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        session()->setFlashdata('import_report', json_encode($report));

        $msg = "{$importados} usuarios importados correctamente.";
        if ($errores) {
            $msg .= "<br>Errores:<br>" . implode('<br>', $errores);
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
        $filename = 'credenciales_' . date('Ymd_His') . '.csv';

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
