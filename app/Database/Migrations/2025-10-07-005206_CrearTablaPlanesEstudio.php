<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaPlanesEstudio extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('planes_estudio')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'carrera_id' => ['type' => 'INT', 'unsigned' => true],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 150],
                'fecha_vigencia' => ['type' => 'DATE', 'null' => true],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('carrera_id', 'carreras', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('planes_estudio');
        }
    }

    public function down()
    {
        $this->forge->dropTable('planes_estudio', true);
    }
}
