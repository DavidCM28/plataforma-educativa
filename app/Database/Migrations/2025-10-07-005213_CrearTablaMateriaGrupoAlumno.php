<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaMateriaGrupoAlumno extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('materia_grupo_alumno')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'grupo_materia_profesor_id' => ['type' => 'INT', 'unsigned' => true],
                'grupo_alumno_id' => ['type' => 'INT', 'unsigned' => true],
                'calificacion_final' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
                'asistencia' => ['type' => 'INT', 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('grupo_materia_profesor_id', 'grupo_materia_profesor', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('grupo_alumno_id', 'grupo_alumno', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('materia_grupo_alumno');
        }
    }

    public function down()
    {
        $this->forge->dropTable('materia_grupo_alumno', true);
    }
}
