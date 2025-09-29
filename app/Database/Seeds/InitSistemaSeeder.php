<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class InitSistemaSeeder extends Seeder
{
    public function run()
    {
        // === 1️⃣ ROLES ===
        $roles = [
            ['nombre' => 'Superusuario', 'descripcion' => 'Acceso total al sistema'],
            ['nombre' => 'Escolares', 'descripcion' => 'Gestión de alumnos, profesores, materias y carreras'],
            ['nombre' => 'Profesor', 'descripcion' => 'Gestión de clases, tareas y calificaciones'],
            ['nombre' => 'Alumno', 'descripcion' => 'Acceso a tareas, calificaciones y perfil personal'],
        ];

        $this->db->table('roles')->insertBatch($roles);

        // Obtenemos IDs (por si se autogeneraron)
        $rolesData = $this->db->table('roles')->get()->getResultArray();
        $rolesMap = [];
        foreach ($rolesData as $rol) {
            $rolesMap[$rol['nombre']] = $rol['id'];
        }

        // === 2️⃣ PERMISOS ===
        $permisos = [
            // --- Sistema base ---
            ['clave' => 'usuarios.ver', 'descripcion' => 'Ver lista de usuarios'],
            ['clave' => 'usuarios.crear', 'descripcion' => 'Crear nuevos usuarios'],
            ['clave' => 'usuarios.editar', 'descripcion' => 'Editar información de usuarios'],
            ['clave' => 'usuarios.eliminar', 'descripcion' => 'Eliminar usuarios'],

            // --- Académico ---
            ['clave' => 'materias.ver', 'descripcion' => 'Ver materias registradas'],
            ['clave' => 'materias.crear', 'descripcion' => 'Crear nuevas materias'],
            ['clave' => 'materias.editar', 'descripcion' => 'Editar materias existentes'],
            ['clave' => 'materias.eliminar', 'descripcion' => 'Eliminar materias'],

            // --- Profesores ---
            ['clave' => 'tareas.ver', 'descripcion' => 'Ver tareas de grupo'],
            ['clave' => 'tareas.crear', 'descripcion' => 'Crear tareas para grupos'],
            ['clave' => 'tareas.editar', 'descripcion' => 'Editar tareas asignadas'],
            ['clave' => 'tareas.eliminar', 'descripcion' => 'Eliminar tareas'],

            // --- Alumnos ---
            ['clave' => 'tareas.entregar', 'descripcion' => 'Entregar tarea asignada'],
            ['clave' => 'perfil.editar', 'descripcion' => 'Editar información personal'],
        ];

        $this->db->table('permisos')->insertBatch($permisos);

        // === 3️⃣ Asignar permisos a roles ===
        $permisosData = $this->db->table('permisos')->get()->getResultArray();
        $permisoMap = [];
        foreach ($permisosData as $perm) {
            $permisoMap[$perm['clave']] = $perm['id'];
        }

        $rolPermisos = [];

        // Superusuario → todos los permisos
        foreach ($permisoMap as $permId) {
            $rolPermisos[] = [
                'rol_id' => $rolesMap['Superusuario'],
                'permiso_id' => $permId,
            ];
        }

        // Escolares → gestión de usuarios y materias (no eliminar)
        foreach (['usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'materias.ver', 'materias.crear', 'materias.editar'] as $clave) {
            $rolPermisos[] = [
                'rol_id' => $rolesMap['Escolares'],
                'permiso_id' => $permisoMap[$clave],
            ];
        }

        // Profesor → tareas
        foreach (['tareas.ver', 'tareas.crear', 'tareas.editar'] as $clave) {
            $rolPermisos[] = [
                'rol_id' => $rolesMap['Profesor'],
                'permiso_id' => $permisoMap[$clave],
            ];
        }

        // Alumno → ver y entregar tareas, editar perfil
        foreach (['tareas.ver', 'tareas.entregar', 'perfil.editar'] as $clave) {
            $rolPermisos[] = [
                'rol_id' => $rolesMap['Alumno'],
                'permiso_id' => $permisoMap[$clave],
            ];
        }

        $this->db->table('rol_permisos')->insertBatch($rolPermisos);

        // === 4️⃣ Superusuario de prueba ===
        $superuser = [
            'nombre' => 'Admin',
            'apellido' => 'Principal',
            'email' => 'admin@plataforma.edu',
            'password' => password_hash('Admin1234', PASSWORD_DEFAULT),
            'rol_id' => $rolesMap['Superusuario'],
            'activo' => true,
            'verificado' => true,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('usuarios')->insert($superuser);

        echo "✅ Seeder completado: roles, permisos, asignaciones y superusuario creados.\n";
    }
}
