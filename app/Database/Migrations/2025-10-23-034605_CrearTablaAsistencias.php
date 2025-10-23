<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaAsistencias extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('asistencias')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true
                ],
                'materia_grupo_alumno_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'fecha' => [
                    'type' => 'DATE',
                    'null' => false,
                ],
                'estado' => [
                    'type' => 'ENUM("asistencia", "falta", "justificada")',
                    'default' => 'asistencia',
                ],
                'frecuencias' => [
                    'type' => 'TINYINT',
                    'default' => 1, // Número de frecuencias (ej. si hubo 2 clases ese día)
                ],
                'observaciones' => [
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
            $this->forge->addForeignKey(
                'materia_grupo_alumno_id',
                'materia_grupo_alumno',
                'id',
                'CASCADE',
                'CASCADE'
            );
            $this->forge->createTable('asistencias');
        }
    }

    public function down()
    {
        $this->forge->dropTable('asistencias');
    }
}
