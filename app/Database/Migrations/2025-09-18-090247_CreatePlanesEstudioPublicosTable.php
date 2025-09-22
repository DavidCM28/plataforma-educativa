<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanesEstudioPublicosTable extends Migration
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
            'carrera_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'nombre' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'descripcion' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'anio' => [
                'type' => 'YEAR',
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
        $this->forge->addForeignKey('carrera_id', 'carreras_publicas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('planes_estudio_publicos');
    }

    public function down()
    {
        $this->forge->dropTable('planes_estudio_publicos');
    }
}
