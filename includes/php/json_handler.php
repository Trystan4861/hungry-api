<?php
/**
 * json_handler
 * Funciones para manejar peticiones JSON en la API
 */

/**
 * get_request_data
 * Obtiene los datos de la petición según el método y tipo de contenido
 * @param string $method Método HTTP (GET, POST, etc.)
 * @return array Datos de la petición
 */
function get_request_data($method) {
    // Obtenemos el tipo de contenido de la petición
    $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    // Para peticiones JSON (común en aplicaciones modernas con Axios, Fetch, etc.)
    if (strpos($content_type, 'application/json') !== false) {
        $json_data = file_get_contents('php://input');
        $json_parsed = json_decode($json_data, true);

        // Si se pudo decodificar el JSON correctamente
        if ($json_parsed !== null) {
            return array_change_key_case($json_parsed, CASE_LOWER);
        }
    }

    // Para peticiones tradicionales (form-data, x-www-form-urlencoded)
    // o si falló la decodificación JSON
    global ${"_$method"};
    return array_change_key_case(${"_$method"}, CASE_LOWER);
}
?>