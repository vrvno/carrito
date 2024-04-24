<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $producto1 = new Producto();
        $producto1->nombre = "Zapatillas";
        $producto1->precio = 40000;
        $producto1->alto = 12;
        $producto1->ancho = 20;
        $producto1->largo = 30;
        $producto1->peso = 1.2;
        $producto1->save();

        $producto2 = new Producto();
        $producto2->nombre = "Agenda";
        $producto2->precio = 5000;
        $producto2->alto = 3;
        $producto2->ancho = 20;
        $producto2->largo = 25;
        $producto2->peso = 0.2;
        $producto2->save();

        $producto3 = new Producto();
        $producto3->nombre = "Celular";
        $producto3->precio = 200000;
        $producto3->alto = 5;
        $producto3->ancho = 10;
        $producto3->largo = 15;
        $producto3->peso = 0.15;
        $producto3->save();

        $producto4 = new Producto();
        $producto4->nombre = "Figura";
        $producto4->precio = 15000;
        $producto4->alto = 20;
        $producto4->ancho = 15;
        $producto4->largo = 15;
        $producto4->peso = 0.1;
        $producto4->save();

        $producto5 = new Producto();
        $producto5->nombre = "Objeto prueba";
        $producto5->precio = 0;
        $producto5->alto = 20;
        $producto5->ancho = 20;
        $producto5->largo = 40;
        $producto5->peso = 1;
        $producto5->save();

        $producto6 = new Producto();
        $producto6->nombre = "Objeto prueba 2";
        $producto6->precio = 0;
        $producto6->alto = 20;
        $producto6->ancho = 20;
        $producto6->largo = 20;
        $producto6->peso = 1;
        $producto6->save();

        $producto6 = new Producto();
        $producto6->nombre = "Objeto enorme";
        $producto6->precio = 100;
        $producto6->alto = 100;
        $producto6->ancho = 100;
        $producto6->largo = 100;
        $producto6->peso = 100;
        $producto6->save();
    }
}
