<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Examenes extends Migration
{
    public function up()
    {
        /*
         * examenes: metadatos del examen
         */
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asignacion_id' => ['type' => 'INT', 'unsigned' => true], // grupo_materia_profesor_id
            'profesor_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 200],
            'descripcion' => ['type' => 'TEXT', 'null' => true],
            'instrucciones' => ['type' => 'TEXT', 'null' => true],
            'tiempo_minutos' => ['type' => 'INT', 'null' => true],
            'puntos_totales' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
            'intentos_maximos' => ['type' => 'INT', 'null' => true],
            'fecha_publicacion' => ['type' => 'DATETIME', 'null' => true],
            'fecha_cierre' => ['type' => 'DATETIME', 'null' => true],
            'estado' => ['type' => 'ENUM', 'constraint' => ['borrador', 'publicado', 'cerrado'], 'default' => 'borrador'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('asignacion_id', 'grupo_materia_profesor', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('examenes');

        /*
         * examen_preguntas: cada reactivo
         */
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'examen_id' => ['type' => 'INT', 'unsigned' => true],
            'tipo' => ['type' => 'ENUM', 'constraint' => ['opcion', 'abierta'], 'default' => 'opcion'],
            'pregunta' => ['type' => 'TEXT'],
            'imagen' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'puntos' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 1],
            'orden' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('examen_id', 'examenes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('examen_preguntas');

        /*
         * examen_opciones: incisos para preguntas de opción múltiple
         */
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pregunta_id' => ['type' => 'INT', 'unsigned' => true],
            'texto' => ['type' => 'TEXT'],
            'es_correcta' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'orden' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pregunta_id', 'examen_preguntas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('examen_opciones');

        /*
         * examen_respuestas: un intento de un alumno
         */
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'examen_id' => ['type' => 'INT', 'unsigned' => true],
            'alumno_id' => ['type' => 'INT', 'unsigned' => true],
            'intento' => ['type' => 'INT', 'unsigned' => true, 'default' => 1],
            'calificacion' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
            'calificado' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'fecha_inicio' => ['type' => 'DATETIME', 'null' => true],
            'fecha_fin' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('examen_id', 'examenes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('examen_respuestas');

        /*
         * examen_respuesta_detalle: respuestas por pregunta
         */
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'respuesta_id' => ['type' => 'INT', 'unsigned' => true],
            'pregunta_id' => ['type' => 'INT', 'unsigned' => true],
            'opcion_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true], // si opción múltiple
            'respuesta_texto' => ['type' => 'TEXT', 'null' => true],                    // si abierta
            'puntos_obtenidos' => ['type' => 'DECIMAL', 'constraint' => '7,2', 'default' => 0],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('respuesta_id', 'examen_respuestas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pregunta_id', 'examen_preguntas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('examen_respuesta_detalle');
    }

    public function down()
    {
        $this->forge->dropTable('examen_respuesta_detalle', true);
        $this->forge->dropTable('examen_respuestas', true);
        $this->forge->dropTable('examen_opciones', true);
        $this->forge->dropTable('examen_preguntas', true);
        $this->forge->dropTable('examenes', true);
    }
}
