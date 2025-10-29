<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaProyectos extends Migration
{
    public function up()
    {
        // ============================================
        // 🧱 Tabla principal: proyectos
        // ============================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'asignacion_id' => ['type' => 'INT', 'unsigned' => true],
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
        $this->forge->createTable('proyectos', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        // ============================================
        // 📂 Tabla secundaria: proyectos_archivos
        // ============================================
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'proyecto_id' => ['type' => 'INT', 'unsigned' => true],
            'archivo' => ['type' => 'VARCHAR', 'constraint' => 255],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('proyecto_id', 'proyectos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('proyectos_archivos', true, ['ENGINE' => 'InnoDB', 'CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);
    }

    public function down()
    {
        $this->forge->dropTable('proyectos_archivos', true);
        $this->forge->dropTable('proyectos', true);
    }
}
