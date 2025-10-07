<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaGrupoMateriaProfesor extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('grupo_materia_profesor')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'grupo_id' => ['type' => 'INT', 'unsigned' => true],
                'materia_id' => ['type' => 'INT', 'unsigned' => true],
                'profesor_id' => ['type' => 'INT', 'unsigned' => true],
                'ciclo_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'ciclo' => ['type' => 'VARCHAR', 'constraint' => 50],
                'aula' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'horario' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('materia_id', 'materias', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('profesor_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('ciclo_id', 'ciclos_academicos', 'id', 'SET NULL', 'CASCADE');
            $this->forge->createTable('grupo_materia_profesor');
        }
    }

    public function down()
    {
        $this->forge->dropTable('grupo_materia_profesor', true);
    }
}
