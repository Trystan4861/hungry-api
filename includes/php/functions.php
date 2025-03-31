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