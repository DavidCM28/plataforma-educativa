<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCarreraIdToGrupos extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('carrera_id', 'grupos')) {
            $this->forge->addColumn('grupos', [
                'carrera_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'after' => 'nombre',
                    'null' => false
                ]
            ]);
            $this->forge->addForeignKey('carrera_id', 'carreras', 'id', 'CASCADE', 'CASCADE');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('carrera_id', 'grupos')) {
            $this->forge->dropForeignKey('grupos', 'grupos_carrera_id_foreign');
            $this->forge->dropColumn('grupos', 'carrera_id');
        }
    }
}
