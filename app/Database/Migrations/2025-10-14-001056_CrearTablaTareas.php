<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaTareas extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('tareas')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'titulo' => ['type' => 'VARCHAR', 'constraint' => 150],
                'descripcion' => ['type' => 'TEXT', 'null' => true],
                'fecha_entrega' => ['type' => 'DATETIME', 'null' => true],
                'archivo_adjunto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'profesor_id' => ['type' => 'INT', 'unsigned' => true],
                'grupo_materia_profesor_id' => ['type' => 'INT', 'unsigned' => true], // ✅ Nuevo campo
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('profesor_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('grupo_materia_profesor_id', 'grupo_materia_profesor', 'id', 'CASCADE', 'CASCADE'); // ✅ Nueva relación
            $this->forge->createTable('tareas');
        }
    }

    public function down()
    {
        $this->forge->dropTable('tareas', true);
    }
}
