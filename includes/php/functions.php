<?php
/**
 * Funciones de utilidad para la API
 */

/**
 * writeLog
 * Escribe un mensaje en el archivo de log
 * @param string $message Mensaje a escribir
 * @param string $level Nivel de log (INFO, WARNING, ERROR)
 * @return void
 */
function writeLog($message, $level = 'INFO') {
    $logFile = __DIR__ . '/../../logs/api.log';
    $logDir = dirname($logFile);

    // Crear directorio de logs si no existe
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Formatear mensaje
    $timestamp = date('Y-m-d H:i:s');
    $formattedMessage = "[$timestamp][$level] $message" . PHP_EOL;

    // Escribir en el log
    error_log($formattedMessage, 3, $logFile);
}

/**
 * checkNullValues
 * Verifica si algún valor en los argumentos dados es nulo o indefinido.
 *
 * @param array $data Arreglo asociativo que contiene los valores a verificar.
 * @param mixed ...$args Lista variable de nombres de claves en el arreglo `$data` que se deben verificar.
 * @return bool Devuelve `true` si alguno de los valores especificados es nulo o indefinido; de lo contrario, devuelve `false`.
 */
function checkNullValues($data,...$args) {
    foreach ($args as $arg) {
        if (!isset($data[$arg]) || $data[$arg] === null) {
            return true;
        }
    }
    return false;
}

/**
 * validate_email
 * Valida un email dado contra una expresión regular
 * @param string $email Email a validar
 * @return boolean True si el email es válido, False en caso contrario
 */
function validate_email($email) {
    $pattern = "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/";
    return preg_match($pattern, $email);
}

/**
 * validate_password
 * Valida una contraseña dada contra una expresión regular
 * @param string $password Contraseña a validar
 * @return boolean True si la contraseña es válida, False en caso contrario
 */
function validate_password($password) {
    $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/";
    return preg_match($pattern, $password);
}

/**
 * formatException
 * Formatea una excepción para el log
 * @param Exception $e Excepción a formatear
 * @return string Mensaje formateado
 */
function formatException($e) {
    return "Exception: " . $e->getMessage() .
           " in " . $e->getFile() .
           " on line " . $e->getLine() .
           "\nStack trace: " . $e->getTraceAsString();
}

// Función recursiva para limpiar datos que podrían causar problemas con json_encode
function cleanForJson($data) {
    if (is_array($data)) {
        foreach ($data as $key => $value) {
            $data[$key] = cleanForJson($value);
        }
        return $data;
    } elseif (is_string($data)) {
        // Convertir a UTF-8 si no lo es
        if (function_exists('mb_detect_encoding')) {
            if (!mb_detect_encoding($data, 'UTF-8', true)) {
                $data = utf8_encode($data);
            }
        } else {
            // Fallback si mb_detect_encoding no está disponible
            $data = utf8_encode($data);
        }
        // Eliminar caracteres no válidos para JSON
        $data = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $data);
        return $data;
    }
    return $data;
}