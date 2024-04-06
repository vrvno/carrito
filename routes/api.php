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
        array_multisort(array_column($array, $atributo), SORT_ASC, $array);
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

    //Funcion para llenar un contenedor con un solo producto (Sin aumentar cajas en caso de que exceda el espacio máximo)
    function elegirContenedor($product, $boxList)
    {
        //Declaración de dimensiones producto
        $alturaProducto = $product['alto'];
        $anchoProducto = $product['ancho'];
        $largoProducto = $product['largo'];
        $volumenProducto = $product['volumetrico'];

        for ($i = 0; $i <= count($boxList); $i++) {

            //Declaració de dimensiones Cajas
            $alturaCaja = $boxList[$i]['alto'];
            $anchoCaja = $boxList[$i]['ancho'];
            $largoCaja = $boxList[$i]['largo'];
            $volumenCaja = $boxList[$i]['volumetrico'];

            //TODO: Implementar caso en que no hayan cajas que puedan contener el producto.
            if ($volumenCaja < $volumenProducto) {
                continue;
            }

            if ($alturaCaja < $alturaProducto || $anchoCaja < $anchoProducto || $largoCaja < $largoProducto) {
                continue;
            } else {  //Aquí se debería implementar una manera de aumentar el número de cajas al aumentar el espacio.
                return 'El producto: ' . $product['nombre'] . ' fue empaquetado en una caja: ' . $boxList[$i]['nombre'];
            }
        }
    }


    function calcularEspacios($object, $box)
    {
        //Array donde se guardan los espacios
        $espacios = [];

        //Declaración de dimanesiones Objeto
        $dimensionObjeto = getDimensiones($object);

        //Declaración de dimensiones Caja
        $dimensionCaja = getDimensiones($box);

        //Cálculo de espacios
        $espacio1 = array(
            'nombre' => 'espacio 1',
            'alto' => $dimensionCaja['alto'] - $dimensionObjeto['alto'],
            'ancho' => $dimensionCaja['ancho'],
            'largo' => $dimensionCaja['largo'],
            'volumetrico' => ($dimensionCaja['alto'] - $dimensionObjeto['alto']) * $dimensionCaja['ancho'] * $dimensionCaja['largo']
        );

        $espacio2 = array(
            'nombre' => 'espacio 2',
            'alto' => $dimensionObjeto['alto'],
            'ancho' => $dimensionCaja['ancho'] - $dimensionObjeto['ancho'],
            'largo' => $dimensionCaja['largo'],
            'volumetrico' => $dimensionObjeto['alto'] * ($dimensionCaja['ancho'] - $dimensionObjeto['ancho']) * $dimensionCaja['largo']
        );

        $espacio3 = array(
            'nombre' => 'espacio 3',
            'alto' => $dimensionObjeto['alto'],
            'ancho' => $dimensionObjeto['ancho'],
            'largo' => $dimensionCaja['largo'] - $dimensionObjeto['largo'],
            'volumetrico' => $dimensionObjeto['alto'] * $dimensionObjeto['ancho'] * ($dimensionCaja['largo'] - $dimensionObjeto['largo'])
        );


        //Agregar espacios a array
        array_push($espacios, $espacio1, $espacio2, $espacio3);
        //Eliminar Espacios sin volumen
        $espacios = eliminarEspacios($espacios);
        //Alternar ancho x largo (si largo < ancho)
        $espacios = ordenarCajas($espacios);


        return $espacios;
    }

    //NOTA: Quizás sea necesario alternar los lados de igual manera (para probar todas las posibilidades)
    function ordenarCajas($boxes)
    {
        foreach ($boxes as $key => &$box) {
            $ancho = $box['ancho'];
            $largo = $box['largo'];

            if ($largo < $ancho) {
                $box['ancho'] = $largo;
                $box['largo'] = $ancho;
            }
        }
        unset($box); // Desvincular la referencia al último elemento
        return $boxes;
    }

    function eliminarEspacios($boxes)
    {
        foreach ($boxes as $key => $box) {
            if ($box['volumetrico'] == 0) {
                unset($boxes[$key]);
            }
        }
        return $boxes;
    }

    function cantidadMax($product, $boxes)
    {
        $total = 0;
        foreach ($boxes as $key => &$box) {
            $largo = floor($box['largo'] / $product['largo']);
            $ancho = floor($box['ancho'] / $product['ancho']);
            $alto = floor($box['alto'] / $product['alto']);
            $volumen = $largo * $ancho * $alto;
            $total += $volumen;

            if ($largo == 0 && $ancho == 0 && $alto == 0) {
                break;
            }

            $espacios = calcularEspacios($product, $box);
            $espacios = ordenarCajas($espacios);

            $total += cantidadMax($product, $espacios); // Sumar la cantidad máxima de las cajas internas
        }

        return $total;
    }

    function calcularBase()
    {
    }




    $Box_list = ordenAscArray($Box_list, 'volumetrico');
    $boxPrueba = [$Box_list[3]];
    return getDimensiones($Box_list[0]);
});
