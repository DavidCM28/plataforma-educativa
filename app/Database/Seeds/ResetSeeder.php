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
        $db->query('TRUNCATE TABLE plan_materias');
        $db->query('TRUNCATE TABLE planes_estudio');
        $db->query('TRUNCATE TABLE materias');
        $db->query('TRUNCATE TABLE carreras');

        // Reactivar restricciones
        $db->query('SET FOREIGN_KEY_CHECKS = 1');

        echo "✅ Tablas reseteadas correctamente.\n";
    }
}
