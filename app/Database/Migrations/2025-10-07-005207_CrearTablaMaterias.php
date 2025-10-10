<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaMaterias extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('materias')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'clave' => ['type' => 'VARCHAR', 'constraint' => 20, 'unique' => true],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
                'creditos' => ['type' => 'INT', 'default' => 0],
                'horas_semana' => ['type' => 'INT', 'default' => 0],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('materias');
        }
    }

    public function down()
    {
        $this->forge->dropTable('materias', true);
    }
}
