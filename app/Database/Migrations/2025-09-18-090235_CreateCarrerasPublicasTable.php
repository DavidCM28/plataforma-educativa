<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCarrerasPublicasTable extends Migration
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
                'constraint' => '255',
            ],
            'slug' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'unique' => true,
            ],
            'nivel' => [
                'type' => 'ENUM',
                'constraint' => ['TSU', 'Ingeniería', 'Licenciatura'],
                'default' => 'TSU',
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'modalidad' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'duracion' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'perfil_ingreso' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'perfil_egreso' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'campo_laboral' => [
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
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('carreras_publicas');
    }

    public function down()
    {
        $this->forge->dropTable('carreras_publicas');
    }
}
