<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ResetSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Desactivar restricciones para truncar
        $db->query('SET FOREIGN_KEY_CHECKS = 0');

        // Orden correcto (dependencias)
        $db->query('TRUNCATE TABLE plan_materias_publicas');
        $db->query('TRUNCATE TABLE planes_estudio_publicos');
        $db->query('TRUNCATE TABLE materias_publicas');
        $db->query('TRUNCATE TABLE carreras_publicas');
        $db->query('TRUNCATE TABLE becas');
        $db->query('TRUNCATE TABLE contactos');
        $db->query('TRUNCATE TABLE permisos');
        $db->query('TRUNCATE TABLE rol_permisos');
        $db->query('TRUNCATE TABLE roles');
        $db->query('TRUNCATE TABLE usuarios');

        // Reactivar restricciones
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        echo "✅ Tablas reseteadas correctamente.\n";
    }
}
