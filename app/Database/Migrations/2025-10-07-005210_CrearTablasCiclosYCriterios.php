<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablasCiclosYCriterios extends Migration
{
    public function up()
    {
        // 🔹 Ciclos Académicos
        if (!$this->db->tableExists('ciclos_academicos')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
                'descripcion' => ['type' => 'TEXT', 'null' => true],
                'num_parciales' => ['type' => 'INT', 'default' => 3],
                'duracion_meses' => ['type' => 'INT', 'default' => 6],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('ciclos_academicos');
        }

        // 🔹 Criterios de Evaluación
        if (!$this->db->tableExists('criterios_evaluacion')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
                'descripcion' => ['type' => 'TEXT', 'null' => true],
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('criterios_evaluacion');
        }

        // 🔹 Ponderaciones por Ciclo
        if (!$this->db->tableExists('ponderaciones_ciclo')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'ciclo_id' => ['type' => 'INT', 'unsigned' => true],
                'parcial_num' => ['type' => 'INT'],
                'criterio_id' => ['type' => 'INT', 'unsigned' => true],
                'porcentaje' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('ciclo_id', 'ciclos_academicos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('criterio_id', 'criterios_evaluacion', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('ponderaciones_ciclo');
        }
    }

    public function down()
    {
        $this->forge->dropTable('ponderaciones_ciclo', true);
        $this->forge->dropTable('criterios_evaluacion', true);
        $this->forge->dropTable('ciclos_academicos', true);
    }
}
