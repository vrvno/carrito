<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Caja;
use App\Models\Producto;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/cotizar', function (Request $request) {
    $Box_DB = Caja::all();
    $Product_request = $request->get('productos'); //Peticion del cliente (el producto seleccionado y la cantidad seleccionada)
    $Product_DB = Producto::whereIn('id', array_column($Product_request, 'id_producto'))->get(); //Muestra los productos concidentes con la petición 

    //Validar las cajas que sean útiles. (Revisar las cajas que sirven)
    //Validar dimensiones.


    //Inicialización de los Arrays
    $Product_list = [];
    $Box_list = [];

    //Array creado según la peticíon (producto y cantidad)
    foreach ($Product_DB as $db) {
        foreach ($Product_request as $product) {
            if ($db->id == $product['id_producto']) {
                for ($i = 0; $i < $product['cantidad']; $i++) {
                    $Product_list[] = array(
                        'id' => $db->id,
                        'nombre' => $db->nombre,
                        'precio' => $db->precio,
                        'peso' => $db->peso,
                        'alto' => $db->alto,
                        'ancho' => $db->ancho,
                        'largo' => $db->largo,
                        'volumetrico' => $db->alto * $db->ancho * $db->largo
                    );
                }
                break;
            }
        }
    }

    //Array con todos los contenedores
    foreach ($Box_DB as $db) {
        $Box_list[] = array(
            'id' => $db->id,
            'nombre' => $db->nombre,
            'alto' => $db->alto,
            'ancho' => $db->ancho,
            'largo' => $db->largo,
            'volumetrico' => $db->alto * $db->ancho * $db->largo
        );
    }

    //Función para ordenar Arrays Ascendente:
    function ordenAscArray($array, $atributo)
    {
        array_multisort(array_column($array, $atributo), SORT_ASC, $array);
        return $array;
    }

    //Función para ordenar Arrays Descendente:
    function ordenDescArray($array, $atributo)
    {
        array_multisort(array_column($array, $atributo), SORT_DESC, $array);
        return $array;
    }

    function ordenarProductos($array)
    {
        usort($array, function ($a, $b) {
            if ($a['ancho'] == $b['ancho']) {
                return $b['alto'] - $a['alto'];
            }
            return $b['ancho'] - $a['ancho'];
        });
        return $array;
    }

    //Funcion para inicializar variables de un objeto
    function getDimensiones($object)
    {
        $dimensiones = [
            $alto = 'alto' => $object['alto'],
            $largo = 'largo' => $object['largo'],
            $ancho = 'ancho' => $object['ancho'],
            $volumetrico = 'volumetrico' => $object['volumetrico']
        ];
        return $dimensiones;
    }

    function cantidadMax($product, $box)
    {
        //Obtener dimensiones
        $producto = getDimensiones($product);
        $cajas = getDimensiones($box);

        $cantidadBase = calcularBase($producto, $cajas);
        $cantidadAltura = floor($box['alto'] / $product['alto']);
        return $cantidadBase * $cantidadAltura;
    }

    function calcularBase($producto, $cajas) //Para un Objeto
    {
        $opcion1 = floor(($cajas['ancho'] / $producto['ancho'])) * floor(($cajas['largo'] / $producto['largo']));
        $opcion2 = floor(($cajas['largo'] / $producto['ancho'])) * floor(($cajas['ancho'] / $producto['largo']));
        $resultado = ($opcion1 >= $opcion2) ? $opcion1 : $opcion2;
        return $resultado;
    }


    // CODIGO QUE FUNCIONA //

    function descartarItems(&$items, $boxes)
    {
        $boxes = ordenAscArray($boxes, 'volumetrico');
        $ultimaCaja = $boxes[count($boxes) - 1];
        $itemsGrandes = [];

        foreach ($items as $key => $item) {
            if ($ultimaCaja['ancho'] < $item['ancho'] || $ultimaCaja['alto'] < $item['alto'] || $ultimaCaja['largo'] < $item['largo']) {

                //Crear caja propia
                $itemsGrandes[] = [
                    "id-caja" => 0,
                    "nombre-caja" => 'Caja de ' . $item['nombre'],
                    "precio total" => $item["precio"],
                    "alto" => $item["alto"],
                    "ancho" => $item["ancho"],
                    "largo" => $item["largo"],
                    "peso" => $item["peso"],
                    "volumetrico" => $item["alto"] * $item["ancho"] * $item["largo"],
                    "producto" => [$item]
                ];

                //Eliminar item del array
                unset($items[$key]);
            }
        }

        return $itemsGrandes;
    }

    //Se usa más tarde para calcular total de items sobrantes.
    function restarCantidades($array1, $array2)
    {
        // Seguimiento de cantidades en el primer array
        $cantidades = [];
        foreach ($array1 as $producto) {
            $id = $producto['id'];
            if (!isset($cantidades[$id])) {
                $cantidades[$id] = 0;
            }
            $cantidades[$id]++;
        }

        // Restar cantidades del segundo array
        foreach ($array2 as $producto) {
            $id = $producto['id'];
            if (isset($cantidades[$id]) && $cantidades[$id] > 0) {
                $cantidades[$id]--;
            }
        }

        // Crear nuevo array con cantidades restantes
        $resultado = [];
        foreach ($cantidades as $id => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $producto = array_values(array_filter($array1, function ($el) use ($id) {
                    return $el['id'] == $id;
                }))[0];
                $resultado[] = $producto;
            }
        }

        return $resultado;
    }

    //añadir una tercera opcion para dejar la lita tal cual (no dividir ni multiplicar)
    function dividirOrden($items, $box, $metodo) //TODO: RESTAR ARRAY ORIGINAL CON EXCEDENTE.
    {
        //ordenar items de mayor a menor
        $items = ordenDescArray($items, 'ancho');
        $conteo = [];

        foreach ($items as $item) {
            $id = $item['id'];
            if (array_key_exists($id, $conteo)) {
                $conteo[$id]['cantidad']++;
            } else {
                $conteo[$id] = [
                    "nombre" => $item["nombre"],
                    "cantidad" => 1,
                    "precio" => $item["precio"],
                    "peso" => $item["peso"],
                    "alto" => $item["alto"],
                    "ancho" => $item["ancho"],
                    "largo" => $item["largo"],
                    "volumetrico" => $item["volumetrico"]
                ];
            }
        }

        if ($metodo === true) {
            //calcular items necesarios en la base// 
            foreach ($conteo as &$item) {
                $cantidadPermitida = floor($box['alto'] / $item['alto']);
                //$item['cantidad'] = floor($box['alto'] / $item['alto']);
                if ($cantidadPermitida < 1) {
                    $cantidadPermitida = 1;
                }
                $item['cantidad'] = ceil($item['cantidad'] / $cantidadPermitida);
            }
        } elseif ($metodo === false) {
            foreach ($conteo as &$item) {
                $cantidadOriginal = floor($box['alto'] / $item['alto']);
                $item['cantidad'] = ceil($item['cantidad'] * $cantidadOriginal); //quizas es floor
            }
        }

        //recorrer conteo y separar items individualmente
        $arraySeparado = [];

        // Iterar sobre el array original
        foreach ($conteo as $id => $elemento) {
            // Obtener la cantidad de elementos
            $cantidad = $elemento['cantidad'];

            // Crear elementos individuales duplicados según la cantidad
            for ($i = 0; $i < $cantidad; $i++) {
                $elementoIndividual = [
                    "id" => $id,
                    "nombre" => $elemento["nombre"],
                    "precio" => $elemento["precio"],
                    "peso" => $elemento["peso"],
                    "alto" => $elemento["alto"],
                    "ancho" => $elemento["ancho"],
                    "largo" => $elemento["largo"],
                    "volumetrico" => $elemento["volumetrico"]
                ];
                // Agregar el elemento individual al array separado
                $arraySeparado[] = $elementoIndividual;
            }
        }
        //AGREGAR CANTIDAD ORIGINAL ACA
        return $arraySeparado;
    }

    function sumarCantidades($items, $atributo)
    {
        $suma = 0;
        foreach ($items as $item) {
            $suma += $item[$atributo];
        }
        return $suma;
    }

    //Llenar cajas
    //TODO: Crear array que contenga dimensiones espacios sobrantes.
    function llenarCaja(&$items, $box, &$itemsAlmacenados)
    {

        foreach ($items as $key => $item) {
            $bin = $box;
            if ($item['ancho'] <= $box['ancho'] && $item['largo'] <= $box['largo'] && $item['alto'] <= $box['alto']) {
                //cambiar espacio disponible de caja
                $box['largo'] -= $item['largo'];
                //modificar bin
                $bin['largo'] = $item['largo'];
                $bin['ancho'] -= $item['ancho'];
                //almacenarItems
                $itemsAlmacenados[] = $item;
                //eliminar item del array
                unset($items[$key]);
                //empezar a recorrer el bin:
                llenarCaja($items, $bin, $itemsAlmacenados);
            }
        }

        if (!empty($items)) {
            $resultado = $items;
        } else {
            $resultado = null;
        }

        $resultado = [$itemsAlmacenados, $resultado];
        return $resultado;
    }

    //Elegir Caja
    //TODO: INTENTAR DAR VUELTA LA RECURSIVIDAD
    function intento($items, $boxes, &$pedido)
    {
        $resultado = [];
        $boxes = ordenAscArray($boxes, 'volumetrico');

        foreach ($boxes as  $box) {
            $itemsTemp = dividirOrden($items, $box, true);
            $arrVacio = [];
            $resultado = llenarCaja($itemsTemp, $box, $arrVacio);

            if (empty($resultado[1])) {

                $pedido[] =
                    [
                        "id-caja" => $box['id'],
                        "nombre-caja" => $box['nombre'],
                        "alto" => $box["alto"],
                        "ancho" => $box["ancho"],
                        "largo" => $box["largo"],
                        "volumetrico" => $box['volumetrico'],
                        "productos" => dividirOrden($resultado[0], $box, false)
                    ];
                break;
            }
        }


        if (!empty($resultado[1])) {
            $ultimaCaja = $boxes[count($boxes) - 1];
            $itemsTemp = dividirOrden($items, $ultimaCaja, true);
            $arrVacio = [];
            $resultado = llenarCaja($itemsTemp, $ultimaCaja, $arrVacio);
            $pedido[] = [
                "id-caja" => $ultimaCaja['id'],
                "nombre-caja" => $ultimaCaja['nombre'],
                "alto" => $ultimaCaja["alto"],
                "ancho" => $ultimaCaja["ancho"],
                "largo" => $ultimaCaja["largo"],
                "volumetrico" => $ultimaCaja['volumetrico'],
                "productos" => dividirOrden($resultado[0], $ultimaCaja, false)
            ];

            //$itemSobrantes = dividirOrden($resultado[1][0], $ultimaCaja, false);
            //el problema es que resultado[1] entra dividido
            foreach ($boxes as $box) { //esta parte del codigo pareciera guardar todos los que no caben exactamente
                $arrVacio = [];
                $pedido = intento(dividirOrden($resultado[1], $box, false), $boxes, $arrVacio);
            }
        }
        return $pedido;
    }

    //añadir una lista que contenga todos los productos almacenados juntos 
    //o añadir otra copia de los items para ir restando los items ingresados, para al final ver los que se ponen

    //los items se podrian ordenar dos veces, por ancho y si tienen el mismo ancho => largo > largo
    function elegirCaja($items, $boxes, &$pedido, &$copiaItems)
    {
        $boxes = ordenAscArray($boxes, 'volumetrico');
        $ultimaCaja = $boxes[count($boxes) - 1];

        //validar si items está vacío
        if (empty($items)) {
            return [];
        }


        $itemsTemp = dividirOrden($items, $ultimaCaja, true);
        $arrVacio = [];
        $resultado = llenarCaja($itemsTemp, $ultimaCaja, $arrVacio);

        if (!empty($resultado[1])) {

            $cantidad = dividirOrden($resultado[0], $ultimaCaja, false);
            $pedido[] = [
                "id-caja" => $ultimaCaja['id'],
                "nombre-caja" => $ultimaCaja['nombre'],
                "precio total" => sumarCantidades($cantidad, 'precio'),
                "alto" => $ultimaCaja["alto"],
                "ancho" => $ultimaCaja["ancho"],
                "largo" => $ultimaCaja["largo"],
                "peso" => sumarCantidades($cantidad, 'peso'),
                "volumetrico" => $ultimaCaja['volumetrico'],
                "productos" => $cantidad
            ];

            $copiaItems = restarCantidades($copiaItems, $cantidad);
            $arrVacio = [];
            $pedido[] = elegirCaja(dividirOrden($resultado[1], $ultimaCaja, false), $boxes, $arrVacio, $copiaItems);
        } elseif (empty($resultado[1])) {
            foreach ($boxes as  $box) {
                $itemsTemp = dividirOrden($items, $box, true);
                $arrVacio = [];
                $resultado = llenarCaja($itemsTemp, $box, $arrVacio);

                if (empty($resultado[1])) {

                    $pedido[] =
                        [
                            "id-caja" => $box['id'],
                            "nombre-caja" => $box['nombre'],
                            "precio total" => sumarCantidades($copiaItems, 'precio'),
                            "alto" => $box["alto"],
                            "ancho" => $box["ancho"],
                            "largo" => $box["largo"],
                            "peso" => sumarCantidades($copiaItems, 'peso'),
                            "volumetrico" => $box['volumetrico'],
                            "productos" => $copiaItems
                        ];
                    break;
                }
            }
        }
        return $pedido;
    }


    $itemsDescartados =  descartarItems($Product_list, $Box_list);
    $arrVacio = [];
    $resultado = elegirCaja($Product_list, $Box_list, $arrVacio, $Product_list);
    $resultado = array_merge($resultado, $itemsDescartados);
    return $resultado;
});


/*
0. Ordenan las cajas 
0.5 Se calcula cual es la caja más grande
0.625 Se ordenan los items por tamaño < ancho.
0.75 Se revisa si todos los productos caben dentro de esa caja
    1. Dividir la orden = Caja
2. Si no caben, se llena la caja más grande con la mayor cantidad de productos
3. Se repite el proceso hasta que no hayan items sobrantes.
4 Se recorren todas las cajas y se elije la que puede almacenar a los restantes.


*/