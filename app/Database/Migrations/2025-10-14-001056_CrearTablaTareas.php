<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaTareas extends Migration
{
    public function up()
    {
        // =========================
        // 📘 Tabla principal tareas
        // =========================
        if (!$this->db->tableExists('tareas')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'asignacion_id' => ['type' => 'INT', 'unsigned' => true], // vincula a grupo_materia_profesor
                'profesor_id' => ['type' => 'INT', 'unsigned' => true],
                'titulo' => ['type' => 'VARCHAR', 'constraint' => 150],
                'descripcion' => ['type' => 'TEXT', 'null' => true],
                'fecha_entrega' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('profesor_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('asignacion_id', 'grupo_materia_profesor', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('tareas');
        }

        // =========================
        // 📎 Archivos de tareas
        // =========================
        if (!$this->db->tableExists('tareas_archivos')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'tarea_id' => ['type' => 'INT', 'unsigned' => true],
                'archivo' => ['type' => 'VARCHAR', 'constraint' => 255],
                'tipo' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('tarea_id', 'tareas', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('tareas_archivos');
        }
    }

    public function down()
    {
        $this->forge->dropTable('tareas_archivos', true);
        $this->forge->dropTable('tareas', true);
    }
}
