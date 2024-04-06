<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Caja;

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $caja1 = new Caja();
        $caja1->nombre = "Caja pequeña";
        $caja1->alto = 10;
        $caja1->ancho = 20;
        $caja1->largo = 30;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja grande";
        $caja1->alto = 30;
        $caja1->ancho = 60;
        $caja1->largo = 60;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja mediana";
        $caja1->alto = 20;
        $caja1->ancho = 30;
        $caja1->largo = 40;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja prueba";
        $caja1->alto = 50;
        $caja1->ancho = 50;
        $caja1->largo = 70;
        $caja1->save();
    }
}
