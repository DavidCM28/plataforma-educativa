<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaAlumnoCarrera extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('alumno_carrera')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'alumno_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'carrera_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'fecha_registro' => [
                    'type' => 'DATE',
                    'null' => true,
                ],
                'estatus' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'Activo',
                ],
            ]);

            $this->forge->addKey('id', true);

            // 🔹 Relaciones
            $this->forge->addForeignKey('alumno_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('carrera_id', 'carreras', 'id', 'CASCADE', 'CASCADE');

            $this->forge->createTable('alumno_carrera');
        }
    }

    public function down()
    {
        $this->forge->dropTable('alumno_carrera');
    }
}
