<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanMateriasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'plan_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'materia_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'ciclo' => [ // Ej: "1", "2", "3"... (cuatrimestre o semestre)
                'type' => 'TINYINT',
                'constraint' => 2,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('plan_id', 'planes_estudio', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('materia_id', 'materias', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('plan_materias');
    }

    public function down()
    {
        $this->forge->dropTable('plan_materias');
    }
}
