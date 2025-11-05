<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTareasEntregas extends Migration
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
            'tarea_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'alumno_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'archivo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'fecha_entrega' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'calificacion' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'null' => true,
            ],
            'retroalimentacion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('tarea_id', 'tareas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('alumno_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tareas_entregas', true);
    }

    public function down()
    {
        $this->forge->dropTable('tareas_entregas', true);
    }
}
