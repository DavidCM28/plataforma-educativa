<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaGruposCarreraGrupo extends Migration
{
    public function up()
    {
        // 🔹 Tabla Grupos
        if (!$this->db->tableExists('grupos')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 50],
                'periodo' => ['type' => 'VARCHAR', 'constraint' => 50],
                'turno' => ['type' => 'ENUM("Matutino","Vespertino","Mixto")', 'default' => 'Matutino'],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('grupos');
        }

        // 🔹 Relación Carrera - Grupo
        if (!$this->db->tableExists('carrera_grupo')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'carrera_id' => ['type' => 'INT', 'unsigned' => true],
                'grupo_id' => ['type' => 'INT', 'unsigned' => true],
                'tutor_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('carrera_id', 'carreras', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('tutor_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('carrera_grupo');
        }
    }

    public function down()
    {
        $this->forge->dropTable('carrera_grupo', true);
        $this->forge->dropTable('grupos', true);
    }
}
