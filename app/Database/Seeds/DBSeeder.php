<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DBSeeder extends Seeder
{
    public function run()
    {
        // Llamamos cada seeder en orden
        $this->call('BecasSeeder');
        $this->call('CarreraSeeder');
        $this->call('InitSistemaSeeder');
        $this->call('MateriaSeeder');
        $this->call('PlanMateriaSeeder');
        $this->call('PlanSeeder');


        echo "✅ Todos los seeders fueron ejecutados correctamente.\n";
    }
}
