<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UsuariosRolesPermisos extends Migration
{
    public function up()
    {
        /**
         * Tabla de Roles
         */
        if (!$this->db->tableExists('roles')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 50],
                'descripcion' => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('roles');
        }

        /**
         * Tabla de Permisos
         */
        if (!$this->db->tableExists('permisos')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'clave' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
                'descripcion' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('permisos');
        }

        /**
         * Tabla pivote: rol_permisos
         */
        if (!$this->db->tableExists('rol_permisos')) {
            $this->forge->addField([
                'rol_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
                'permiso_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                ],
            ]);

            $this->forge->addKey(['rol_id', 'permiso_id'], true);
            $this->forge->addForeignKey('rol_id', 'roles', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('permiso_id', 'permisos', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('rol_permisos');
        }

        /**
         * Tabla de Usuarios
         */
        if (!$this->db->tableExists('usuarios')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'nombre' => ['type' => 'VARCHAR', 'constraint' => 100],
                'apellido' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'email' => ['type' => 'VARCHAR', 'constraint' => 150, 'unique' => true],
                'password' => ['type' => 'VARCHAR', 'constraint' => 255],
                'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],

                'rol_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],

                // Identificadores únicos por tipo de usuario
                'matricula' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
                'num_empleado' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],

                // Control de estado
                'activo' => ['type' => 'BOOLEAN', 'default' => true],
                'verificado' => ['type' => 'BOOLEAN', 'default' => false],

                // Auditoría
                'ultimo_login' => ['type' => 'DATETIME', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
                'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('rol_id', 'roles', 'id', 'CASCADE', 'SET NULL');
            $this->forge->createTable('usuarios');
        }
    }

    public function down()
    {
        $this->forge->dropTable('usuarios', true);
        $this->forge->dropTable('rol_permisos', true);
        $this->forge->dropTable('permisos', true);
        $this->forge->dropTable('roles', true);
    }
}
