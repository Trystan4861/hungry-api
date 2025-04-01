<?php
/**
 * getData.inc.php
 * Endpoint para obtener datos del servidor sin sincronización automática
 *
 * Este endpoint devuelve los datos del servidor sin realizar ninguna sincronización
 * con los datos del cliente. Es útil para que el cliente pueda comparar los datos
 * y decidir qué sincronizar.
 */

// Verificar que los datos necesarios estén presentes
if (!isset($data['token']) || !isset($data['fingerid'])) {
    $json["error_msg"] = "Faltan datos obligatorios para obtener los datos";
    return;
}

// Verificar que el usuario esté autenticado correctamente
if (!$user->isLoaded()) {
    $json["error_msg"] = $msgerrors["token_error"];
    return;
}

try {
    // Inicializar la respuesta con los datos básicos
    $responseData = [
        "loginData" => [
            "email" => $user->getUser()["email"],
            "token" => $data['token'],
            "fingerID" => $data['fingerid'],
            "logged" => true
        ]
    ];

    // Obtener categorías del servidor
    $responseData['categorias'] = $categorias->getCategorias();

    // Obtener supermercados del servidor
    $responseData['supermercados'] = $supermercados->getSupermercados();

    // Obtener productos del servidor
    $responseData['productos'] = $productos->getProductos();

    // Devolver los datos
    $json["data"] = $responseData;
} catch (Exception $e) {
    $json["error_msg"] = "Error al obtener los datos: " . $e->getMessage();
}