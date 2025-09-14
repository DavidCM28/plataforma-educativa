<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactos extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'correo' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'telefono' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'mensaje' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('contactos');
    }

    public function down()
    {
        $this->forge->dropTable('contactos');
    }
}
