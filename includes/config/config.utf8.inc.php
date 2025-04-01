<?php
// Verificar si la extensión mbstring está cargada
if (!extension_loaded('mbstring')) {
    // Registrar el error pero continuar
    error_log("La extensión mbstring no está disponible. Algunas funcionalidades de codificación pueden no funcionar correctamente.");
} else {
    // Configuración para manejar correctamente UTF-8MB4 (incluyendo emojis)
    if (function_exists('mb_internal_encoding')) {
        mb_internal_encoding('UTF-8');
    }
    if (function_exists('mb_http_output')) {
        mb_http_output('UTF-8');
    }
    // No usamos mb_http_input ya que solo sirve para detectar la codificación, no para establecerla
    if (function_exists('mb_regex_encoding')) {
        mb_regex_encoding('UTF-8');
    }
}

// Asegurarse de que la configuración de PHP esté correcta para manejar UTF-8
ini_set('default_charset', 'UTF-8');