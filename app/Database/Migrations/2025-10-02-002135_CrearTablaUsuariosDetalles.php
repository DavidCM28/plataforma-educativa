<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaUsuariosDetalles extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('usuarios_detalles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'usuario_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],

                // 📘 DATOS PERSONALES
                'sexo' => ['type' => 'ENUM("Masculino", "Femenino")', 'null' => true],
                'fecha_nacimiento' => ['type' => 'DATE', 'null' => true],
                'estado_civil' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
                'curp' => ['type' => 'VARCHAR', 'constraint' => 18, 'null' => true],
                'rfc' => ['type' => 'VARCHAR', 'constraint' => 13, 'null' => true],
                'pais_origen' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],

                // ❤️ DATOS MÉDICOS
                'peso' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'null' => true],
                'estatura' => ['type' => 'DECIMAL', 'constraint' => '4,2', 'null' => true],
                'tipo_sangre' => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
                'antecedente_diabetico' => ['type' => 'BOOLEAN', 'default' => false],
                'antecedente_hipertenso' => ['type' => 'BOOLEAN', 'default' => false],
                'antecedente_cardiaco' => ['type' => 'BOOLEAN', 'default' => false],

                // 🏠 DOMICILIO
                'estado' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'municipio' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'colonia' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'calle' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'numero_exterior' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'numero_interior' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],

                // 📞 COMUNICACIÓN
                'telefono' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'correo_alternativo' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'telefono_trabajo' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],

                // 🎓 FORMACIÓN ACADÉMICA
                'grado_academico' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'descripcion_grado' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'cedula_profesional' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],

                // 🕓 AUDITORÍA
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('usuarios_detalles');
        }
    }

    public function down()
    {
        $this->forge->dropTable('usuarios_detalles', true);
    }
}
