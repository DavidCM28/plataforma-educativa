<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaGrupoAlumno extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('grupo_alumno')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'grupo_id' => ['type' => 'INT', 'unsigned' => true],
                'alumno_id' => ['type' => 'INT', 'unsigned' => true],
                'fecha_inscripcion' => ['type' => 'DATE', 'null' => true],
                'estatus' => ['type' => 'ENUM("Inscrito","Baja","Egresado")', 'default' => 'Inscrito'],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('grupo_id', 'grupos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('alumno_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('grupo_alumno');
        }
    }

    public function down()
    {
        $this->forge->dropTable('grupo_alumno', true);
    }
}
