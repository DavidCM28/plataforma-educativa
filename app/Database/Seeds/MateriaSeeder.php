<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class MateriaSeeder extends Seeder
{
    public function run()
    {
        $data = [
            // ===== TRONCO COMÚN (25) =====
            ['nombre' => 'Matemáticas I', 'descripcion' => 'Álgebra y trigonometría.', 'creditos' => 5],
            ['nombre' => 'Matemáticas II', 'descripcion' => 'Cálculo diferencial.', 'creditos' => 6],
            ['nombre' => 'Matemáticas III', 'descripcion' => 'Cálculo integral.', 'creditos' => 6],
            ['nombre' => 'Matemáticas IV', 'descripcion' => 'Álgebra lineal.', 'creditos' => 6],
            ['nombre' => 'Matemáticas V', 'descripcion' => 'Probabilidad y estadística.', 'creditos' => 6],
            ['nombre' => 'Física I', 'descripcion' => 'Mecánica clásica.', 'creditos' => 5],
            ['nombre' => 'Física II', 'descripcion' => 'Electricidad y magnetismo.', 'creditos' => 5],
            ['nombre' => 'Inglés I', 'descripcion' => 'Inglés básico.', 'creditos' => 4],
            ['nombre' => 'Inglés II', 'descripcion' => 'Inglés intermedio.', 'creditos' => 4],
            ['nombre' => 'Inglés III', 'descripcion' => 'Inglés avanzado.', 'creditos' => 4],
            ['nombre' => 'Comunicación Oral y Escrita', 'descripcion' => 'Expresión académica.', 'creditos' => 4],
            ['nombre' => 'Ética y Valores', 'descripcion' => 'Responsabilidad profesional.', 'creditos' => 4],
            ['nombre' => 'Taller de Investigación I', 'descripcion' => 'Metodología de la investigación.', 'creditos' => 4],
            ['nombre' => 'Taller de Investigación II', 'descripcion' => 'Proyecto aplicado.', 'creditos' => 4],
            ['nombre' => 'Proyecto Integrador I', 'descripcion' => 'Resolución de problemas prácticos.', 'creditos' => 6],
            ['nombre' => 'Proyecto Integrador II', 'descripcion' => 'Prototipo o modelo aplicado.', 'creditos' => 6],
            ['nombre' => 'Desarrollo Humano', 'descripcion' => 'Habilidades blandas.', 'creditos' => 4],
            ['nombre' => 'Formación Sociocultural', 'descripcion' => 'Cultura y sociedad.', 'creditos' => 4],
            ['nombre' => 'Tecnologías de la Información Básicas', 'descripcion' => 'Ofimática.', 'creditos' => 4],
            ['nombre' => 'Metodología de la Programación', 'descripcion' => 'Lógica y algoritmos básicos.', 'creditos' => 6],
            ['nombre' => 'Estadística Aplicada', 'descripcion' => 'Análisis de datos.', 'creditos' => 6],
            ['nombre' => 'Derecho Laboral', 'descripcion' => 'Legislación básica.', 'creditos' => 4],
            ['nombre' => 'Medio Ambiente y Sustentabilidad', 'descripcion' => 'Desarrollo sostenible.', 'creditos' => 4],
            ['nombre' => 'Innovación y Creatividad', 'descripcion' => 'Pensamiento creativo.', 'creditos' => 4],
            ['nombre' => 'Emprendimiento', 'descripcion' => 'Creación de empresas.', 'creditos' => 6],

            // ===== INGENIERÍA TIC (15) =====
            ['nombre' => 'Programación I', 'descripcion' => 'Fundamentos en C.', 'creditos' => 6],
            ['nombre' => 'Programación II', 'descripcion' => 'POO en Java.', 'creditos' => 6],
            ['nombre' => 'Programación III', 'descripcion' => 'Estructuras avanzadas.', 'creditos' => 6],
            ['nombre' => 'Bases de Datos I', 'descripcion' => 'Modelo relacional y SQL.', 'creditos' => 6],
            ['nombre' => 'Bases de Datos II', 'descripcion' => 'Optimización y seguridad.', 'creditos' => 6],
            ['nombre' => 'Redes I', 'descripcion' => 'Fundamentos de redes.', 'creditos' => 6],
            ['nombre' => 'Redes II', 'descripcion' => 'Seguridad y protocolos.', 'creditos' => 6],
            ['nombre' => 'Desarrollo Web I', 'descripcion' => 'HTML, CSS, JS.', 'creditos' => 6],
            ['nombre' => 'Desarrollo Web II', 'descripcion' => 'Back-end con PHP.', 'creditos' => 6],
            ['nombre' => 'Sistemas Operativos', 'descripcion' => 'Linux y Windows.', 'creditos' => 6],
            ['nombre' => 'Inteligencia Artificial', 'descripcion' => 'ML básico.', 'creditos' => 6],
            ['nombre' => 'Seguridad Informática', 'descripcion' => 'Ciberseguridad básica.', 'creditos' => 6],
            ['nombre' => 'Arquitectura de Computadoras', 'descripcion' => 'Hardware y ensamblador.', 'creditos' => 6],
            ['nombre' => 'Computación en la Nube', 'descripcion' => 'Servicios cloud.', 'creditos' => 6],
            ['nombre' => 'Desarrollo Móvil', 'descripcion' => 'Apps Android.', 'creditos' => 6],

            // ===== MECATRÓNICA (15) =====
            ['nombre' => 'Dibujo Técnico', 'descripcion' => 'Interpretación de planos.', 'creditos' => 5],
            ['nombre' => 'Circuitos Eléctricos', 'descripcion' => 'Análisis de circuitos básicos.', 'creditos' => 6],
            ['nombre' => 'Electrónica Analógica', 'descripcion' => 'Dispositivos semiconductores.', 'creditos' => 6],
            ['nombre' => 'Electrónica Digital', 'descripcion' => 'Sistemas digitales.', 'creditos' => 6],
            ['nombre' => 'Mecánica', 'descripcion' => 'Estática y dinámica.', 'creditos' => 6],
            ['nombre' => 'Neumática e Hidráulica', 'descripcion' => 'Control de fluidos.', 'creditos' => 6],
            ['nombre' => 'Sensores y Actuadores', 'descripcion' => 'Automatización básica.', 'creditos' => 6],
            ['nombre' => 'Control Automático', 'descripcion' => 'Modelado y control.', 'creditos' => 6],
            ['nombre' => 'Robótica', 'descripcion' => 'Cinemática de robots.', 'creditos' => 6],
            ['nombre' => 'PLC y Automatización', 'descripcion' => 'Controladores industriales.', 'creditos' => 6],
            ['nombre' => 'Materiales de Ingeniería', 'descripcion' => 'Propiedades mecánicas.', 'creditos' => 6],
            ['nombre' => 'Termodinámica', 'descripcion' => 'Calor y energía.', 'creditos' => 6],
            ['nombre' => 'Manufactura Avanzada', 'descripcion' => 'Procesos automatizados.', 'creditos' => 6],
            ['nombre' => 'Diseño Mecánico', 'descripcion' => 'Modelado CAD.', 'creditos' => 6],
            ['nombre' => 'Electromagnetismo', 'descripcion' => 'Aplicaciones industriales.', 'creditos' => 6],

            // ===== NEGOCIOS (10) =====
            ['nombre' => 'Administración General', 'descripcion' => 'Principios de gestión.', 'creditos' => 6],
            ['nombre' => 'Contabilidad I', 'descripcion' => 'Estados financieros.', 'creditos' => 6],
            ['nombre' => 'Contabilidad II', 'descripcion' => 'Costos y presupuestos.', 'creditos' => 6],
            ['nombre' => 'Economía I', 'descripcion' => 'Microeconomía.', 'creditos' => 6],
            ['nombre' => 'Economía II', 'descripcion' => 'Macroeconomía.', 'creditos' => 6],
            ['nombre' => 'Mercadotecnia', 'descripcion' => 'Estrategias comerciales.', 'creditos' => 6],
            ['nombre' => 'Finanzas', 'descripcion' => 'Análisis financiero.', 'creditos' => 6],
            ['nombre' => 'Gestión de Proyectos', 'descripcion' => 'Metodologías ágiles.', 'creditos' => 6],
            ['nombre' => 'Comportamiento Organizacional', 'descripcion' => 'Liderazgo y equipos.', 'creditos' => 6],
            ['nombre' => 'Negocios Internacionales', 'descripcion' => 'Globalización.', 'creditos' => 6],

            // ===== EDUCACIÓN (10) =====
            ['nombre' => 'Introducción a la Educación', 'descripcion' => 'Conceptos básicos.', 'creditos' => 6],
            ['nombre' => 'Psicología Educativa', 'descripcion' => 'Procesos de aprendizaje.', 'creditos' => 6],
            ['nombre' => 'Didáctica General', 'descripcion' => 'Métodos de enseñanza.', 'creditos' => 6],
            ['nombre' => 'Planeación Curricular', 'descripcion' => 'Diseño de programas.', 'creditos' => 6],
            ['nombre' => 'Evaluación Educativa', 'descripcion' => 'Métodos de evaluación.', 'creditos' => 6],
            ['nombre' => 'Tecnología Educativa', 'descripcion' => 'Uso de TIC en la enseñanza.', 'creditos' => 6],
            ['nombre' => 'Educación Inclusiva', 'descripcion' => 'Atención a la diversidad.', 'creditos' => 6],
            ['nombre' => 'Historia de la Educación', 'descripcion' => 'Evolución de sistemas educativos.', 'creditos' => 6],
            ['nombre' => 'Práctica Docente I', 'descripcion' => 'Experiencia en aula.', 'creditos' => 8],
            ['nombre' => 'Práctica Docente II', 'descripcion' => 'Aplicación avanzada.', 'creditos' => 10],
        ];

        // Agregar created_at y updated_at a cada registro
        $data = array_map(function ($item) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $item['updated_at'] = date('Y-m-d H:i:s');
            return $item;
        }, $data);

        // Insertar en la base de datos
        $this->db->table('materias_publicas')->insertBatch($data);
    }
}
