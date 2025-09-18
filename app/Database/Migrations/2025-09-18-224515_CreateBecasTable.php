<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBecasTable extends Migration
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
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'porcentaje' => [
                'type' => 'INT',
                'constraint' => 3,
                'comment' => 'Porcentaje de cobertura en colegiatura',
            ],
            'requisitos' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Texto con los requisitos principales',
            ],
            'servicio_becario_horas' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => true,
                'default' => 0,
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
        $this->forge->createTable('becas');
    }

    public function down()
    {
        $this->forge->dropTable('becas');
    }
}
