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
        $caja1->nombre = "Caja mediana 2";
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

        $caja1 = new Caja();
        $caja1->nombre = "Caja chica 1";
        $caja1->alto = 6;
        $caja1->ancho = 10;
        $caja1->largo = 20;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja chica 2";
        $caja1->alto = 12;
        $caja1->ancho = 14;
        $caja1->largo = 22;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja chica";
        $caja1->alto = 12;
        $caja1->ancho = 20;
        $caja1->largo = 33;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja mediana";
        $caja1->alto = 18;
        $caja1->ancho = 33;
        $caja1->largo = 44;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja multipropósito";
        $caja1->alto = 10;
        $caja1->ancho = 27;
        $caja1->largo = 39;
        $caja1->save();

        $caja1 = new Caja();
        $caja1->nombre = "Caja grande";
        $caja1->alto = 30;
        $caja1->ancho = 40;
        $caja1->largo = 60;
        $caja1->save();
    }
}
