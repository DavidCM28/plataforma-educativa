<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CarreraSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'nombre' => 'Ingeniería en Tecnologías de la Información e Innovación Digital',
                'slug' => 'ingenieria-tecnologias-de-la-informacion-e-innovacion-digital',
                'nivel' => 'Ingeniería',
                'modalidad' => 'Escolarizado',
                'duracion' => '9 cuatrimestres',
                'descripcion' => 'Formación en desarrollo de software, seguridad informática, redes y tecnologías digitales.',
                'perfil_ingreso' => 'Interés en programación, matemáticas y nuevas tecnologías.',
                'perfil_egreso' => 'Diseñar e implementar soluciones innovadoras en el área de tecnologías de la información.',
                'campo_laboral' => 'Desarrollo de software, ciberseguridad, administración de redes, innovación digital.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Ingeniería Mecatrónica',
                'slug' => 'ingenieria-mecatronica',
                'nivel' => 'Ingeniería',
                'modalidad' => 'Escolarizado',
                'duracion' => '9 cuatrimestres',
                'descripcion' => 'Formación integral en automatización, control y robótica aplicada.',
                'perfil_ingreso' => 'Afinidad por la electrónica, física y matemáticas.',
                'perfil_egreso' => 'Capacidad para diseñar y mantener sistemas mecatrónicos y de automatización industrial.',
                'campo_laboral' => 'Automatización industrial, robótica, manufactura avanzada, control de procesos.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Ingeniería en Mantenimiento Industrial',
                'slug' => 'ingenieria-mantenimiento-industrial',
                'nivel' => 'Ingeniería',
                'modalidad' => 'Escolarizado',
                'duracion' => '9 cuatrimestres',
                'descripcion' => 'Profesionales capacitados en gestión y mantenimiento de equipos industriales.',
                'perfil_ingreso' => 'Interés en procesos industriales y administración técnica.',
                'perfil_egreso' => 'Gestionar planes de mantenimiento industrial preventivo y correctivo.',
                'campo_laboral' => 'Industrias manufactureras, mantenimiento de maquinaria, gestión de procesos industriales.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Licenciatura en Negocios y Mercadotecnia',
                'slug' => 'licenciatura-negocios-mercadotecnia',
                'nivel' => 'Licenciatura',
                'modalidad' => 'Escolarizado',
                'duracion' => '9 cuatrimestres',
                'descripcion' => 'Formación en estrategias comerciales, mercadotecnia digital y emprendimiento.',
                'perfil_ingreso' => 'Interés en administración, ventas y comunicación.',
                'perfil_egreso' => 'Diseñar y aplicar estrategias de mercadotecnia para negocios competitivos.',
                'campo_laboral' => 'Empresas comerciales, marketing digital, consultoría empresarial, emprendimiento.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nombre' => 'Licenciatura en Educación',
                'slug' => 'licenciatura-educacion',
                'nivel' => 'Licenciatura',
                'modalidad' => 'Escolarizado',
                'duracion' => '9 cuatrimestres',
                'descripcion' => 'Formación de docentes con habilidades pedagógicas y uso de tecnología educativa.',
                'perfil_ingreso' => 'Vocación por la enseñanza y compromiso social.',
                'perfil_egreso' => 'Diseñar e implementar estrategias didácticas innovadoras en distintos niveles educativos.',
                'campo_laboral' => 'Instituciones educativas, capacitación empresarial, investigación pedagógica.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        $this->db->table('carreras_publicas')->insertBatch($data);
    }
}
