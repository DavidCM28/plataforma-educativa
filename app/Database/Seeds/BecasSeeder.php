<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class BecasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nombre' => 'Beca de Empleado',
                'descripcion' => 'Apoyo del 100% en colegiatura para empleados de la institución.',
                'porcentaje' => 100,
                'requisitos' => 'Promedio mínimo de 7.0 para nuevo ingreso. Renovación con promedio ≥ 9.0. No tener adeudos ni otra beca. Solicitar carta laboral.',
                'servicio_becario_horas' => 0,
            ],
            [
                'nombre' => 'Beca de Excelencia',
                'descripcion' => 'Apoyo del 100% en colegiatura para alumnos con alto desempeño académico.',
                'porcentaje' => 100,
                'requisitos' => 'Promedio ≥ 9.5. No tener adeudos ni otra beca. No tener revaloración final.',
                'servicio_becario_horas' => 60,
            ],
            [
                'nombre' => 'Beca Referenciada',
                'descripcion' => 'Apoyo del 95% en colegiatura para alumnos con carta de dependencia gubernamental o municipal.',
                'porcentaje' => 95,
                'requisitos' => 'Promedio nuevo ingreso ≥ 7.0, reinscripción ≥ 9.0. No tener adeudos ni otra beca. Entregar carta de referencia.',
                'servicio_becario_horas' => 150,
            ],
            [
                'nombre' => 'Beca de Discapacidad',
                'descripcion' => 'Apoyo del 100% en colegiatura para alumnos con discapacidad.',
                'porcentaje' => 100,
                'requisitos' => 'No tener adeudos. No tener otro tipo de beca.',
                'servicio_becario_horas' => 0,
            ],
        ];

        $this->db->table('becas')->insertBatch($data);
    }
}
