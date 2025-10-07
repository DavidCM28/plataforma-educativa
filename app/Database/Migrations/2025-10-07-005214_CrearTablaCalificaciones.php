<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaCalificaciones extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('calificaciones')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'materia_grupo_alumno_id' => ['type' => 'INT', 'unsigned' => true],
                'parcial' => ['type' => 'VARCHAR', 'constraint' => 10],
                'calificacion' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
                'observaciones' => ['type' => 'TEXT', 'null' => true],
                'fecha_registro' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('materia_grupo_alumno_id', 'materia_grupo_alumno', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('calificaciones');
        }
    }

    public function down()
    {
        $this->forge->dropTable('calificaciones', true);
    }
}
