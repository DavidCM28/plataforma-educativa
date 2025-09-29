<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'carrera_id' => 1,
                'nombre' => 'Plan 2025 - Ingeniería en TIC',
                'descripcion' => 'Plan de estudios 2025 para Ingeniería en Tecnologías de la Información e Innovación Digital',
                'anio' => 2025,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'carrera_id' => 2,
                'nombre' => 'Plan 2025 - Ingeniería Mecatrónica',
                'descripcion' => 'Plan de estudios 2025 para Ingeniería Mecatrónica',
                'anio' => 2025,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'carrera_id' => 3,
                'nombre' => 'Plan 2025 - Ingeniería en Mantenimiento Industrial',
                'descripcion' => 'Plan de estudios 2025 para Ingeniería en Mantenimiento Industrial',
                'anio' => 2025,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'carrera_id' => 4,
                'nombre' => 'Plan 2025 - Licenciatura en Negocios y Mercadotecnia',
                'descripcion' => 'Plan de estudios 2025 para Licenciatura en Negocios y Mercadotecnia',
                'anio' => 2025,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'carrera_id' => 5,
                'nombre' => 'Plan 2025 - Licenciatura en Educación',
                'descripcion' => 'Plan de estudios 2025 para Licenciatura en Educación',
                'anio' => 2025,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('planes_estudio_publicos')->insertBatch($data);
    }
}
