<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PlanMateriaSeeder extends Seeder
{
    public function run()
    {
        $data = [];

        // === TRONCO COMÚN PARA TODAS LAS CARRERAS (cuatrimestres 1–3) ===
        $troncoComun = [
            1 => ['Matemáticas I', 'Física I', 'Inglés I', 'Comunicación Oral y Escrita', 'Ética y Valores', 'Desarrollo Humano'],
            2 => ['Matemáticas II', 'Física II', 'Inglés II', 'Formación Sociocultural', 'Metodología de la Programación', 'Medio Ambiente y Sustentabilidad'],
            3 => ['Matemáticas III', 'Inglés III', 'Estadística Aplicada', 'Proyecto Integrador I', 'Innovación y Creatividad', 'Emprendimiento'],
        ];

        // === PLANES DE ESTUDIO (IDs según PlanSeeder) ===
        $planes = [
            1 => 'TIC',
            2 => 'Mecatrónica',
            3 => 'Mantenimiento',
            4 => 'Negocios',
            5 => 'Educación',
        ];

        // === MATERIAS ESPECIALIZADAS POR CARRERA (a partir de cuatri 4) ===
        $especialidades = [
            'TIC' => [
                4 => ['Programación I', 'Bases de Datos I', 'Redes I', 'Sistemas Operativos'],
                5 => ['Programación II', 'Bases de Datos II', 'Redes II', 'Desarrollo Web I'],
                6 => ['Programación III', 'Desarrollo Web II', 'Inteligencia Artificial', 'Arquitectura de Computadoras'],
                7 => ['Computación en la Nube', 'Seguridad Informática', 'Proyecto Integrador II'],
                8 => ['Desarrollo Móvil', 'Taller de Investigación I'],
                9 => ['Taller de Investigación II'],
            ],
            'Mecatrónica' => [
                4 => ['Dibujo Técnico', 'Circuitos Eléctricos', 'Electrónica Analógica'],
                5 => ['Electrónica Digital', 'Mecánica', 'Neumática e Hidráulica'],
                6 => ['Sensores y Actuadores', 'Control Automático', 'Robótica'],
                7 => ['PLC y Automatización', 'Termodinámica', 'Materiales de Ingeniería'],
                8 => ['Manufactura Avanzada', 'Proyecto Integrador II'],
                9 => ['Diseño Mecánico', 'Electromagnetismo'],
            ],
            'Mantenimiento' => [
                4 => ['Circuitos Eléctricos', 'Electrónica Analógica', 'Mecánica'],
                5 => ['Electrónica Digital', 'Materiales de Ingeniería', 'Neumática e Hidráulica'],
                6 => ['Control Automático', 'Sensores y Actuadores'],
                7 => ['PLC y Automatización', 'Termodinámica'],
                8 => ['Proyecto Integrador II', 'Taller de Investigación I'],
                9 => ['Taller de Investigación II'],
            ],
            'Negocios' => [
                4 => ['Administración General', 'Contabilidad I', 'Economía I'],
                5 => ['Contabilidad II', 'Economía II', 'Mercadotecnia'],
                6 => ['Finanzas', 'Gestión de Proyectos'],
                7 => ['Comportamiento Organizacional', 'Negocios Internacionales'],
                8 => ['Proyecto Integrador II', 'Taller de Investigación I'],
                9 => ['Taller de Investigación II'],
            ],
            'Educación' => [
                4 => ['Introducción a la Educación', 'Psicología Educativa'],
                5 => ['Didáctica General', 'Planeación Curricular'],
                6 => ['Evaluación Educativa', 'Tecnología Educativa'],
                7 => ['Educación Inclusiva', 'Historia de la Educación'],
                8 => ['Práctica Docente I'],
                9 => ['Práctica Docente II'],
            ],
        ];

        // === ARMADO DE DATA ===
        foreach ($planes as $planId => $clave) {
            // Tronco común
            foreach ($troncoComun as $ciclo => $materias) {
                foreach ($materias as $nombreMateria) {
                    $materia = $this->db->table('materias_publicas')->where('nombre', $nombreMateria)->get()->getRow();
                    if ($materia) {
                        $data[] = [
                            'plan_id' => $planId,
                            'materia_id' => $materia->id,
                            'ciclo' => $ciclo,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
            // Especialidad
            foreach ($especialidades[$clave] as $ciclo => $materias) {
                foreach ($materias as $nombreMateria) {
                    $materia = $this->db->table('materias_publicas')->where('nombre', $nombreMateria)->get()->getRow();
                    if ($materia) {
                        $data[] = [
                            'plan_id' => $planId,
                            'materia_id' => $materia->id,
                            'ciclo' => $ciclo,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                }
            }
        }

        // Insertar
        $this->db->table('plan_materias_publicas')->insertBatch($data);
    }
}
