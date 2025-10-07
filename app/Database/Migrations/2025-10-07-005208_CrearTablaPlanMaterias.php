<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaPlanMaterias extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('plan_materias')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'plan_id' => ['type' => 'INT', 'unsigned' => true],
                'materia_id' => ['type' => 'INT', 'unsigned' => true],
                'cuatrimestre' => ['type' => 'INT', 'default' => 1],
                'tipo' => ['type' => 'ENUM("Tronco Común","Especialidad","Optativa")', 'default' => 'Tronco Común'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('plan_id', 'planes_estudio', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('materia_id', 'materias', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('plan_materias');
        }
    }

    public function down()
    {
        $this->forge->dropTable('plan_materias', true);
    }
}
