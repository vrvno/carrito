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

    //TODO: ESTA FUNCION YA NO FUNCIONA, PORQUE EL ROTAR AHORA NO ACEPTA ARRAYS COMO PARAMETRO
    function calcularEspacios($object, $box)
    {
        //Array donde se guardan los espacios
        $espacios = [];

        //Declaración de dimanesiones Objeto
        $objeto = getDimensiones($object);

        //Declaración de dimensiones Caja
        $caja = getDimensiones($box);

        //Cálculo de espacios
        $espacio1 = array(
            'nombre' => 'espacio 1',
            'alto' => $caja['alto'] - $objeto['alto'],
            'ancho' => $caja['ancho'],
            'largo' => $caja['largo'],
            'volumetrico' => ($caja['alto'] - $objeto['alto']) * $caja['ancho'] * $caja['largo']
        );

        $espacio2 = array(
            'nombre' => 'espacio 2',
            'alto' => $objeto['alto'],
            'ancho' => $caja['ancho'] - $objeto['ancho'],
            'largo' => $caja['largo'],
            'volumetrico' => $objeto['alto'] * ($caja['ancho'] - $objeto['ancho']) * $caja['largo']
        );

        $espacio3 = array(
            'nombre' => 'espacio 3',
            'alto' => $objeto['alto'],
            'ancho' => $objeto['ancho'],
            'largo' => $caja['largo'] - $objeto['largo'],
            'volumetrico' => $objeto['alto'] * $objeto['ancho'] * ($caja['largo'] - $objeto['largo'])
        );


        //Agregar espacios a array
        array_push($espacios, $espacio1, $espacio2, $espacio3);
        //Eliminar Espacios sin volumen
        $espacios = eliminarEspacios($espacios);
        //Alternar ancho x largo (si largo < ancho)

        //$espacios = rotarCajas($espacios);


        return $espacios;
    }

    //NOTA: Quizás sea mejor no usar el array como parametro (CAMBIADO)
    function rotarCajas($box)
    {
        $ancho = $box['ancho'];
        $largo = $box['largo'];

        $box['ancho'] = $largo;
        $box['largo'] = $ancho;

        unset($box); // Desvincular la referencia al último elementoj
        return $box;
    }

    function eliminarEspacios($boxes) //En Array
    {
        foreach ($boxes as $key => $box) {
            if ($box['volumetrico'] == 0) {
                unset($boxes[$key]);
            }
        }
        return $boxes;
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

    //logica de un objeto, luego pasar a distintos objetos (los parametros pasan a ser arrays)
    function llenarCajax($products, $box)
    {
        //Inicializar variable
        $espacioUsado = 0;
        $espacioLateral = 0;
        $espacioSuperior = 0;

        foreach ($products as $key => $product) {
            //Llenar espacio USADO con el primer objeto
            if ($espacioUsado == 0) {
                //1. Espacio usado
                $espacioUsado = getDimensiones($product);
                //Eliminar producto del array
                unset($products[$key]);
                //2. Espacio libre "superior"
                $espacioSuperior = crearBase($box['largo'] - $espacioUsado['largo'], $box['ancho']);
                //3. Espacio libre "lateral"
                $espacioLateral = crearBase($box['ancho'] - $espacioUsado['ancho'], $espacioUsado['largo']);
                continue;
            }
            if ($product['ancho'] <= $espacioLateral['ancho'] && $espacioLateral['largo'] <= $product['largo']) {
            }
        }
    }

    function crearBase($ancho, $largo)
    {
        $box = array(
            'ancho' => $ancho,
            'largo' =>  $largo,
            'base' => $largo * $ancho
        );
        return $box;
    }

    // CODIGO QUE FUNCIONA //

    function dividirOrden($items, $box) //TODO: RESTAR ARRAY ORIGINAL CON EXCEDENTE.
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

        //calcular items necesarios en la base// 
        foreach ($conteo as &$item) {
            $cantidadPermitida = floor($box['alto'] / $item['alto']);
            //$item['cantidad'] = floor($box['alto'] / $item['alto']);
            if ($cantidadPermitida < 1) {
                $cantidadPermitida = 1;
            }
            $item['cantidad'] = ceil($item['cantidad'] / $cantidadPermitida);
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

    /*Los items que entren a esta funcion ya van a estar depurados, por lo que para conseguir los items que de verdad esten 
    sobrando se deberá crear un array con al diferencia de los itemsTemporales (antes de la division) y los items Almacenados */

    //TODO: Cambiar funcion, cosa que retorne los productos que fueron almacenados, no es relevante para esta funcion pero si para la de elegir cajas
    function llenarCaja(&$items, &$box)
    {
        //COPIA DE ARRAY ITEMS
        $itemsTemp = $items;
        //Items ya almacenados
        $itemsAlmacenados = [];
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
                llenarCaja($items, $bin);
            }
        }

        if (empty($items)) {
            return $itemsTemp;
            //return 'cupieron';
        } else {
            $resultado = [$itemsAlmacenados, $items];
            return $resultado;
            //IMPLEMENTAR LOGICA DE REVERSION PARA RECUPERAR LISTA DE ITEMS QUE SI CUPIERON
            //return 'no cupieron';
        }
    }

    //Elegir Caja V1
    /*
    function elegirCaja(&$items, &$boxes)
    {

        $itemsTemp = $items; //sin modificaciones
        $boxes = ordenAscArray($boxes, 'volumetrico');
        foreach ($boxes as $box) {
            $items = dividirOrden($items, $box);
            $resultado = llenarCaja($items, $box);

            //validar viabilidad por volumen total del pedido
            if (empty($items)) {
                $pedido[] =
                    [
                        "id-caja" => $box['id'],
                        "nombre-caja" => $box['nombre'],
                        "alto" => $box["alto"],
                        "ancho" => $box["ancho"],
                        "largo" => $box["largo"],
                        "volumetrico" => $box['volumetrico'],
                        "productos" => $itemsTemp
                    ];
                break;
            }
        }
        //Lógica: Si todos los items no entran en una caja, se utiliza la caja más grande y los items sobrantes entrar al loop nuevamente
        // Complicaciones con hacer orden con productos
        //Intentar elegir unicamente las cajas (para ver si funciona el correctamente el flujo)
        if (!empty($items)) {
            // Crear array con items sobrantes
            /*
            $copiaItems = $itemsTemp;
            $itemsTemp = dividirOrden($itemsTemp, $boxes[count($boxes) - 1]);
            $almacenados = llenarCaja($itemsTemp, $boxes[count($boxes) - 1]);
            $pedido[] = [
                "id-caja" => $boxes[count($boxes) - 1]['id'],
                "nombre-caja" => $boxes[count($boxes) - 1]['nombre'],
                "alto" => $boxes[count($boxes) - 1]["alto"],
                "ancho" => $boxes[count($boxes) - 1]["ancho"],
                "largo" => $boxes[count($boxes) - 1]["largo"],
                "volumetrico" => $boxes[count($boxes) - 1]['volumetrico'],
                "productos" => $almacenados
            ];
            // Agregar los resultados de la nueva caja al pedido actual 

            //Invertir valores del array(multiplicando por cantidad permitida verticalmente)
            $itemSobrantes = [];
            foreach ($almacenados as $almacenado) {
                $cantidad = $boxes[count($boxes) - 1]['alto'] / $almacenado['alto'];
                for ($i = 0; $i < $cantidad; $i++) {
                    $itemSobrantes[] = $almacenado; //puede haber un error acá
                }
            }

            $itemSobrantes = restarCantidades($copiaItems, $itemSobrantes); //SE DEBE INVERTIR LA CANTIDAD
            $pedido = array_merge($pedido, elegirCaja($itemSobrantes, $boxes)); 
            $resultado = llenarCaja($itemsTemp, $boxes[count($boxes) - 1]);
            $pedido[] = [
                "id-caja" => $boxes[count($boxes) - 1]['id'],
                "nombre-caja" => $boxes[count($boxes) - 1]['nombre']
            ];
            $pedido = array_merge($pedido, elegirCaja($itemsTemp, $boxes));
        }

        return $pedido;
    } */

    function elegirCaja(&$items, &$boxes)
    {

        $itemsTemp = $items; //sin modificaciones
        $boxes = ordenAscArray($boxes, 'volumetrico');
        foreach ($boxes as $box) {
            $items = dividirOrden($items, $box);
            $resultado = llenarCaja($items, $box);

            //validar viabilidad por volumen total del pedido
            if (empty($items)) {
                $pedido[] =
                    [
                        "id-caja" => $box['id'],
                        "nombre-caja" => $box['nombre'],
                        "alto" => $box["alto"],
                        "ancho" => $box["ancho"],
                        "largo" => $box["largo"],
                        "volumetrico" => $box['volumetrico'],
                        "productos" => $itemsTemp
                    ];
                break;
            }
        }
        //Lógica: Si todos los items no entran en una caja, se utiliza la caja más grande y los items sobrantes entrar al loop nuevamente
        // Complicaciones con hacer orden con productos
        //Intentar elegir unicamente las cajas (para ver si funciona el correctamente el flujo)
        if (!empty($items)) {
            // Crear array con items sobrantes
            /*
            $copiaItems = $itemsTemp;
            $itemsTemp = dividirOrden($itemsTemp, $boxes[count($boxes) - 1]);
            $almacenados = llenarCaja($itemsTemp, $boxes[count($boxes) - 1]);
            $pedido[] = [
                "id-caja" => $boxes[count($boxes) - 1]['id'],
                "nombre-caja" => $boxes[count($boxes) - 1]['nombre'],
                "alto" => $boxes[count($boxes) - 1]["alto"],
                "ancho" => $boxes[count($boxes) - 1]["ancho"],
                "largo" => $boxes[count($boxes) - 1]["largo"],
                "volumetrico" => $boxes[count($boxes) - 1]['volumetrico'],
                "productos" => $almacenados
            ];
            // Agregar los resultados de la nueva caja al pedido actual 

            //Invertir valores del array(multiplicando por cantidad permitida verticalmente)
            $itemSobrantes = [];
            foreach ($almacenados as $almacenado) {
                $cantidad = $boxes[count($boxes) - 1]['alto'] / $almacenado['alto'];
                for ($i = 0; $i < $cantidad; $i++) {
                    $itemSobrantes[] = $almacenado; //puede haber un error acá
                }
            }

            $itemSobrantes = restarCantidades($copiaItems, $itemSobrantes); //SE DEBE INVERTIR LA CANTIDAD
            $pedido = array_merge($pedido, elegirCaja($itemSobrantes, $boxes)); */
            $resultado = llenarCaja($itemsTemp, $boxes[count($boxes) - 1]);
            $pedido[] = [
                "id-caja" => $boxes[count($boxes) - 1]['id'],
                "nombre-caja" => $boxes[count($boxes) - 1]['nombre']
            ];
            $pedido = array_merge($pedido, elegirCaja($itemsTemp, $boxes));
        }

        return $pedido;
    }

    //SI EL PROBLEMA ES LA DIVISION ES MEJOR QUE LAS FUNCIONES SE LLAMEN INDIVIDUALMENTE A UNA FUNCION EXTERNA
    //SE DIVIDE UNA VEZ JUSTO ANTES DE ENTRAR A LA FUNCION

    function intento(&$items, &$boxes, $copiaItems)
    {
        $boxes = ordenAscArray($boxes, 'volumetrico');
        foreach ($boxes as  $box) {
            $items = dividirOrden($items, $box);
            $resultado = llenarCaja($items, $box);

            if (empty($items)) {
                $pedido[] =
                    [
                        "id-caja" => $box['id'],
                        "nombre-caja" => $box['nombre'],
                        "alto" => $box["alto"],
                        "ancho" => $box["ancho"],
                        "largo" => $box["largo"],
                        "volumetrico" => $box['volumetrico'],
                        "productos" => $copiaItems
                    ];
                break;
            }
        }

        if (!empty($items)) {
            $itemsTemp = $copiaItems;
            $itemsTemp = dividirOrden($itemsTemp, $boxes[count($boxes) - 1]); //Nota, si se usa $items quiza no sea necesario volver a dividirlo.
            $resultado = llenarCaja($itemsTemp, $boxes[count($boxes) - 1]); //Array que contiene: $itemsAlmacenados[0] / $itemsRestantes[1]
            $pedido[] = [
                "id-caja" => $boxes[count($boxes) - 1]['id'],
                "nombre-caja" => $boxes[count($boxes) - 1]['nombre'],
                "alto" => $boxes[count($boxes) - 1]["alto"],
                "ancho" => $boxes[count($boxes) - 1]["ancho"],
                "largo" => $boxes[count($boxes) - 1]["largo"],
                "volumetrico" => $boxes[count($boxes) - 1]['volumetrico'],
                "productos" => $resultado[0]
            ];



            $pedido = array_merge($pedido, elegirCaja($resultado[1], $boxes));
        }
        return $pedido;
    }

    /*
     */
    $json1 = '[
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 6,
            "nombre": "Objeto prueba 2",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 20,
            "volumetrico": 8000
        },
        {
            "id": 6,
            "nombre": "Objeto prueba 2",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 20,
            "volumetrico": 8000
        },
        {
            "id": 6,
            "nombre": "Objeto prueba 2",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 20,
            "volumetrico": 8000
        }
    ]';

    $json2 = '[
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 5,
            "nombre": "Objeto prueba",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 40,
            "volumetrico": 16000
        },
        {
            "id": 6,
            "nombre": "Objeto prueba 2",
            "precio": 0,
            "peso": 1,
            "alto": 20,
            "ancho": 20,
            "largo": 20,
            "volumetrico": 8000
        }
    ]';

    $arrayPrueba1 = json_decode($json1, true);
    $arrayPrueba2 = json_decode($json2, true);

    // $array ahora es un array PHP que contiene los objetos convertidos del JSON



    //return elegirCaja($Product_list, $Box_list);
    //return dividirOrden($Product_list, $Box_list[3]);
    //return llenarCaja($Product_list, $Box_list[3]);
    //return $Product_list;
    //return $Product_request;
    //return restarCantidades($arrayPrueba1, $arrayPrueba2);
    return intento($Product_list, $Box_list, $Product_list);
});
