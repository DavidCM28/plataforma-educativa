<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CrearTablaPublicaciones extends Migration
{
    public function up()
    {
        /*
        |---------------------------------------------------------------
        | 📰 TABLA: publicaciones_grupo
        |---------------------------------------------------------------
        */
        if (!$this->db->tableExists('publicaciones_grupo')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'grupo_materia_profesor_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'usuario_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'tipo' => [
                    'type' => 'ENUM("aviso", "recurso", "foro")',
                    'default' => 'aviso',
                ],
                'contenido' => [
                    'type' => 'TEXT',
                    'null' => false,
                ],
                'fecha_publicacion' => [
                    'type' => 'TIMESTAMP',
                    'null' => false,
                    // ⚠️ Sin default aquí, lo manejará MySQL automáticamente o desde PHP
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('grupo_materia_profesor_id', 'grupo_materia_profesor', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('publicaciones_grupo');
        }

        /*
        |---------------------------------------------------------------
        | 📎 TABLA: publicaciones_archivos
        |---------------------------------------------------------------
        */
        if (!$this->db->tableExists('publicaciones_archivos')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'publicacion_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'archivo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => false,
                ],
                'tipo' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'default' => 'archivo',
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('publicacion_id', 'publicaciones_grupo', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('publicaciones_archivos');
        }

        /*
        |---------------------------------------------------------------
        | 💬 TABLA: publicaciones_comentarios
        |---------------------------------------------------------------
        */
        if (!$this->db->tableExists('publicaciones_comentarios')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'publicacion_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'usuario_id' => [
                    'type' => 'INT',
                    'unsigned' => true,
                ],
                'comentario' => [
                    'type' => 'TEXT',
                    'null' => false,
                ],
                'fecha' => [
                    'type' => 'TIMESTAMP',
                    'null' => false,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addForeignKey('publicacion_id', 'publicaciones_grupo', 'id', 'CASCADE', 'CASCADE');
            $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('publicaciones_comentarios');
        }
    }

    public function down()
    {
        $this->forge->dropTable('publicaciones_comentarios', true);
        $this->forge->dropTable('publicaciones_archivos', true);
        $this->forge->dropTable('publicaciones_grupo', true);
    }
}
