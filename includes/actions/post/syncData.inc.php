<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Log de datos recibidos

/**
 * sincronizarCategorias
 * Sincroniza las categorías entre el cliente y el servidor
 *
 * @param Categorias $categorias Objeto de categorías del servidor
 * @param array $clientCategorias Categorías del cliente
 * @return array Categorías sincronizadas
 */
function sincronizarCategorias($categorias, $clientCategorias) {
    // Obtener categorías del servidor
    $serverCategorias = $categorias->getCategorias();

    // Crear un mapa de categorías del servidor por ID para facilitar la búsqueda
    $serverCategoriasMap = [];
    foreach ($serverCategorias as $categoria) {
        $serverCategoriasMap[$categoria['id_categoria']] = $categoria;
    }

    // Array para almacenar las categorías sincronizadas
    $categoriasSync = [];

    // Procesar categorías del cliente
    foreach ($clientCategorias as $clientCategoria) {
        $id = $clientCategoria['id'];

        //comprobar si el timestamp es una cadena en formato ISO8601
        //si es así, convertirlo a timestamp
        if (is_string($clientCategoria['timestamp']) && strtotime($clientCategoria['timestamp']) !== false) {
            $clientTimestamp= strtotime($clientCategoria['timestamp']);
        }
        else{ // Convertir el timestamp a segundos si está en milisegundos
            $clientTimestamp = isset($clientCategoria['timestamp']) ?
                (strlen((string)$clientCategoria['timestamp']) > 10 ?
                    floor($clientCategoria['timestamp'] / 1000) :
                    $clientCategoria['timestamp']) :
                0;
        }
        // Si la categoría existe en el servidor
        if (isset($serverCategoriasMap[$id])) {
            $serverCategoria = $serverCategoriasMap[$id];
            $serverTimestamp = $serverCategoria['timestamp'];

            // Si el timestamp del cliente es más reciente, actualizar en el servidor
            if ($clientTimestamp > $serverTimestamp) {
                // Actualizar en el servidor
                $categorias->updateCategoriaText($id, $clientCategoria['text']);
                $categorias->updateCategoriaVisible($id, $clientCategoria['visible'] ? 1 : 0);

                // Usar la versión del cliente para la respuesta
                $categoriasSync[] = [
                    'id' => $id,
                    'text' => $clientCategoria['text'],
                    'bgColor' => $serverCategoria['bgColor'], // Mantener el color del servidor
                    'visible' => $clientCategoria['visible'],
                    'timestamp' => $clientCategoria['timestamp'] // Convertir a milisegundos para la respuesta
                ];
            } else {
                // Usar la versión del servidor para la respuesta
                $categoriasSync[] = [
                    'id' => $id,
                    'text' => $serverCategoria['text'],
                    'bgColor' => $serverCategoria['bgColor'],
                    'visible' => $serverCategoria['visible'] ? true : false,
                    'timestamp' => $serverCategoria['timestamp'] // Convertir a milisegundos para la respuesta
                ];
            }

            // Marcar como procesada
            unset($serverCategoriasMap[$id]);
        } else {
            // La categoría no existe en el servidor, añadirla
            // Por ahora, incluimos la categoría del cliente en la respuesta
            $categoriasSync[] = [
                'id' => $id,
                'text' => $clientCategoria['text'],
                'bgColor' => $clientCategoria['bgColor'],
                'visible' => $clientCategoria['visible'],
                'timestamp' => $clientCategoria['timestamp'] // Convertir a milisegundos para la respuesta
            ];
        }
    }

    // Añadir categorías del servidor que no están en el cliente
    foreach ($serverCategoriasMap as $id => $serverCategoria) {
        $categoriasSync[] = [
            'id' => $id,
            'text' => $serverCategoria['text'],
            'bgColor' => $serverCategoria['bgColor'],
            'visible' => $serverCategoria['visible'] ? true : false,
            'timestamp' => $serverCategoria['timestamp'] * 1000 // Convertir a milisegundos para la respuesta
        ];
    }

    return $categoriasSync;
}
/**
 * sincronizarSupermercados
 * Sincroniza los supermercados entre el cliente y el servidor
 *
 * @param Supermercados $supermercados Objeto de supermercados del servidor
 * @param array $clientSupermercados Supermercados del cliente
 * @return array Supermercados sincronizados
 */
function sincronizarSupermercados($supermercados, $clientSupermercados) {
    // Obtener supermercados del servidor
    $serverSupermercados = $supermercados->getSupermercados();
    // Crear un mapa de supermercados del servidor por ID para facilitar la búsqueda
    $serverSupermercadosMap = [];
    foreach ($serverSupermercados as $supermercado) {
        $serverSupermercadosMap[$supermercado['id']] = $supermercado;
    }

    // Array para almacenar los supermercados sincronizados
    $supermercadosSync = [];

    // Procesar supermercados del cliente
    foreach ($clientSupermercados as $clientSupermercado) {
        $id = $clientSupermercado['id'];

        //comprobar si el timestamp es una cadena en formato ISO8601
        //si es así, convertirlo a timestamp
        if (is_string($clientSupermercado['timestamp']) && strtotime($clientSupermercado['timestamp']) !== false) {
            $clientTimestamp= strtotime($clientSupermercado['timestamp']);
        }
        else{
        // Convertir el timestamp a segundos si está en milisegundos
        $clientTimestamp = isset($clientSupermercado['timestamp']) ?
            (strlen((string)$clientSupermercado['timestamp']) > 10 ?
                floor($clientSupermercado['timestamp'] / 1000) :
                $clientSupermercado['timestamp']) :
            0;
        }
        // Si el supermercado existe en el servidor
        if (isset($serverSupermercadosMap[$id])) {
            $serverSupermercado = $serverSupermercadosMap[$id];
            $serverTimestamp = isset($serverSupermercado['timestamp']) ? $serverSupermercado['timestamp'] : 0;

            // Si el timestamp del cliente es más reciente, actualizar en el servidor
            if ($clientTimestamp > $serverTimestamp) {
                // Usar la versión del cliente para la respuesta
                $supermercadosSync[] = [
                    'id' => $id,
                    'text' => $clientSupermercado['text'],
                    'logo' => $clientSupermercado['logo'],
                    'visible' => $clientSupermercado['visible'],
                    'order' => $clientSupermercado['order'],
                    'timestamp' => $clientSupermercado['timestamp'] // Convertir a milisegundos para la respuesta
                ];
            } else {
                // Usar la versión del servidor para la respuesta
                $supermercadosSync[] = [
                    'id' => $id,
                    'text' => $serverSupermercado['text'],
                    'logo' => $serverSupermercado['logo'],
                    'visible' => isset($serverSupermercado['visible']) ? $serverSupermercado['visible'] : true,
                    'order' => $serverSupermercado['order']?? 0,
                    'timestamp' => $serverSupermercado['timestamp'] // Convertir a milisegundos para la respuesta
                ];
            }

            // Marcar como procesada
            unset($serverSupermercadosMap[$id]);
        } else {
            // El supermercado no existe en el servidor, añadirlo
            // Por ahora, incluimos el supermercado del cliente en la respuesta
            $supermercadosSync[] = [
                'id' => $id,
                'text' => $clientSupermercado['text'],
                'logo' => $clientSupermercado['logo'],
                'visible' => $clientSupermercado['visible'],
                'order' => $clientSupermercado['order'],
                'timestamp' => $clientSupermercado['timestamp'] // Convertir a milisegundos para la respuesta
            ];
        }
    }

    // Añadir supermercados del servidor que no están en el cliente
    foreach ($serverSupermercadosMap as $id => $serverSupermercado) {
        $supermercadosSync[] = [
            'id' => $id,
            'text' => $serverSupermercado['text'],
            'logo' => $serverSupermercado['logo'],
            'visible' => isset($serverSupermercado['visible']) ? $serverSupermercado['visible'] : true,
            'order' => isset($serverSupermercado['order']) ? $serverSupermercado['order'] : 0,
            'timestamp' => (isset($serverSupermercado['timestamp']) ? $serverSupermercado['timestamp'] : time()) * 1000 // Convertir a milisegundos para la respuesta
        ];
    }

    return $supermercadosSync;
}

/**
 * sincronizarProductos
 * Sincroniza los productos entre el cliente y el servidor
 *
 * @param Productos $productos Objeto de productos del servidor
 * @param array $clientProductos Productos del cliente
 * @return array Productos sincronizados
 */
function sincronizarProductos($productos, $clientProductos) {
    global $user;
    $userId = $user->getId();



    try {
        // Obtener productos del servidor para este usuario
        $serverProductos = $productos->getProductos();

        // Crear mapa usando id como clave
        $serverProductosMap = [];
        foreach ($serverProductos as $producto) {
            $serverProductosMap[$producto['id']] = $producto;
        }

        $productosSync = [];

        foreach ($clientProductos as $clientProducto) {
            $idProducto = $clientProducto['id'];


            try {
                // Convertir timestamp
                //comprobar si el timestamp es una cadena en formato ISO8601
                //si es así, convertirlo a timestamp
                if (is_string($clientProducto['timestamp']) && strtotime($clientProducto['timestamp']) !== false) {
                    $clientTimestamp= strtotime($clientProducto['timestamp']);
                }
                else{

                $clientTimestamp = isset($clientProducto['timestamp']) ?
                    (strlen((string)$clientProducto['timestamp']) > 10 ?
                        floor($clientProducto['timestamp'] / 1000) :
                        $clientProducto['timestamp']) :
                    0;
                }
                // Preparar datos del producto
                $productoData = [
                    'id_producto' => $idProducto,
                    'fk_id_usuario' => $userId,
                    'text' => $clientProducto['text'],
                    'fk_id_categoria' => $clientProducto['id_categoria'],
                    'fk_id_supermercado' => $clientProducto['id_supermercado'],
                    'amount' => isset($clientProducto['amount']) ? $clientProducto['amount'] : 1,
                    'selected' => isset($clientProducto['selected']) ? ($clientProducto['selected'] ? 1 : 0) : 0,
                    'done' => isset($clientProducto['done']) ? ($clientProducto['done'] ? 1 : 0) : 0,
                    'timestamp' => $clientProducto['timestamp'] // Convertir a milisegundos para la respuesta
                ];

                // Si existe el producto para este usuario
                if (isset($serverProductosMap[$idProducto])) {

                    $serverProducto = $serverProductosMap[$idProducto];

                    // Actualizar producto existente
                    $productos->updateProducto($productoData);

                    $productosSync[] = [
                        'id' => $idProducto,
                        'text' => $clientProducto['text'],
                        'id_categoria' => $clientProducto['id_categoria'],
                        'id_supermercado' => $clientProducto['id_supermercado'],
                        'selected' => $clientProducto['selected'],
                        'done' => $clientProducto['done'],
                        'amount' => $clientProducto['amount'],
                        'timestamp' => $clientProducto['timestamp']
                    ];
                } else {

                    // Crear nuevo producto con el id_producto del cliente
                    if ($productos->newProducto($productoData)) {
                        $productosSync[] = [
                            'id' => $idProducto,
                            'text' => $clientProducto['text'],
                            'id_categoria' => $clientProducto['id_categoria'],
                            'id_supermercado' => $clientProducto['id_supermercado'],
                            'selected' => $clientProducto['selected'],
                            'done' => $clientProducto['done'],
                            'amount' => $clientProducto['amount'],
                            'timestamp' => $clientProducto['timestamp']
                        ];
                    } else {
                        $json["error_msg"]="Error al crear producto $idProducto";
                    }
                }

                unset($serverProductosMap[$idProducto]);
            } catch (Exception $e) {
                $json["error_msg"]="Error procesando producto $idProducto: " . $e->getMessage();
                continue;
            }
        }

        // Añadir productos del servidor que no están en el cliente
        foreach ($serverProductosMap as $idProducto => $serverProducto) {
            $productosSync[] = [
                'id' => $serverProducto['id_producto'],
                'text' => $serverProducto['text'],
                'id_categoria' => $serverProducto['fk_id_categoria'],
                'id_supermercado' => $serverProducto['fk_id_supermercado'],
                'selected' => $serverProducto['selected'] ? true : false,
                'done' => $serverProducto['done'] ? true : false,
                'amount' => $serverProducto['amount'],
                'timestamp' => $serverProducto['timestamp']
            ];
        }

        return $productosSync;
    } catch (Exception $e) {
        $json["error_msg"]="Error general en sincronización: " . $e->getMessage();
    }
}
// Verificar que los datos necesarios estén presentes
if (!isset($data['token']) || !isset($data['fingerid']) || !isset($data['data'])) {
    $json["error_msg"]="Faltan datos obligatorios para la sincronización: " . print_r($data, true);
    return;
}

// Verificar que el usuario esté autenticado correctamente
if (!$user->isLoaded()) {
    $json["error_msg"]="Usuario no autenticado. Token: " . ($data['token'] ?? 'no token');
    return;
}

// Verificar que el fingerID esté asociado a la cuenta del usuario
if ($user->getDevice() == 0) {
    $json["error_msg"] = "Dispositivo no autorizado";
    return;
}

try {
    // Obtener los datos del cliente
    $clientData = $data['data'];

    // Inicializar la respuesta con los datos básicos
    $responseData = [
        "appName" => isset($clientData['appName']) ? $clientData['appName'] : '',
        "maxLenght" => isset($clientData['maxLenght']) ? $clientData['maxLenght'] : 0,
        "defaultTabActive" => isset($clientData['defaultTabActive']) ? $clientData['defaultTabActive'] : 0,
        "alturaDisponible" => isset($clientData['alturaDisponible']) ? $clientData['alturaDisponible'] : 0,
        "fullScreen" => isset($clientData['fullScreen']) ? $clientData['fullScreen'] : false,
        "loginData" => [
            "email" => $data['email'],
            "token" => $data['token'],
            "fingerID" => $data['fingerid'],
            "logged" => true
        ]
    ];

    // Sincronizar categorías si existen
    if (isset($clientData['categorias'])) {
        $responseData['categorias'] = sincronizarCategorias($categorias, $clientData['categorias']);
    } else {
        $responseData['categorias'] = $categorias->getCategorias();
    }

    // Sincronizar supermercados si existen
    if (isset($clientData['supermercados'])) {
        $responseData['supermercados'] = sincronizarSupermercados($supermercados, $clientData['supermercados']);
    } else {
        $responseData['supermercados'] = $supermercados->getSupermercados();
    }

    // Sincronizar productos si existen
    if (isset($clientData['productos'])) {
        $responseData['productos'] = sincronizarProductos($productos, $clientData['productos']);
    } else {
        $responseData['productos'] = $productos->getProductos();
    }

    // Devolver los datos sincronizados
    $json["data"] = $responseData;
} catch (Exception $e) {
    $json["error_msg"] = "Error en la sincronización: " . $e->getMessage();
    // Registrar el error para depuración

}
