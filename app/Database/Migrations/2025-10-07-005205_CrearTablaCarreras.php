<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaCarreras extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('carreras')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
                'siglas' => ['type' => 'VARCHAR', 'constraint' => 20],
                'duracion' => ['type' => 'INT', 'null' => true],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('carreras');
        }
    }

    public function down()
    {
        $this->forge->dropTable('carreras', true);
    }
}
