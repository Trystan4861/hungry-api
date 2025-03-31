<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';
/**
 * configurarPHPMailer
 * Configura y devuelve un objeto PHPMailer listo para enviar correos mediante SMTP
 * utilizando la configuración del archivo .env
 *
 * @param int $debugLevel Nivel de depuración (0-4)
 * @param mixed $debugCallback Función de callback para la salida de depuración
 * @return PHPMailer Objeto PHPMailer configurado o false en caso de error
 */
function configurarPHPMailer($debugLevel = 0, $debugCallback = null) {
    global $env;

    // Verificar que existan las configuraciones necesarias
    if (!isset($env['MAIL']) || !isset($env['MAIL']['HOST']) || !isset($env['MAIL']['USER']) || !isset($env['MAIL']['PASS'])) {
        error_log("Error: Faltan configuraciones de correo en el archivo .env");
        return false;
    }

    // Crear una instancia de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor
        $mail->SMTPDebug  = $debugLevel;            // Nivel de debug: 0 = sin mensajes, 1 = mensajes cliente, 2 = mensajes cliente y servidor

        // Configurar la salida de depuración
        if ($debugCallback !== null && is_callable($debugCallback)) {
            $mail->Debugoutput = $debugCallback;
        } else {
            $mail->Debugoutput = function($str, $level) {
                echo "PHPMailer Debug ($level): $str<br>";
                error_log("PHPMailer Debug ($level): $str");
            };
        }

        $mail->isSMTP();                            // Usar SMTP
        $mail->Host       = $env['MAIL']['HOST'];   // Servidor SMTP
        $mail->SMTPAuth   = true;                   // Habilitar autenticación SMTP
        $mail->Username   = $env['MAIL']['USER'];   // Usuario SMTP
        $mail->Password   = $env['MAIL']['PASS'];   // Contraseña SMTP

        // Configurar seguridad según el valor de SECURE en .env
        if (isset($env['MAIL']['SECURE'])) {
            if (strtolower($env['MAIL']['SECURE']) === 'ssl') {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                error_log("Usando cifrado SSL para correo");
            } else {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                error_log("Usando cifrado TLS para correo");
            }
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            error_log("Usando cifrado TLS por defecto para correo");
        }

        $mail->Port = $env['MAIL']['PORT'];         // Puerto TCP para conectarse

        // Configuración de timeout
        $mail->Timeout = 30;                        // Timeout en segundos

        // Remitente (usando el mismo correo de configuración)
        $mail->setFrom($env['APP']['EMAIL_USER'], 'Sistema de Verificación ' . $env['APP']['NAME']);
        $mail->addReplyTo($env['APP']['EMAIL_USER'], 'Soporte ' . $env['APP']['NAME']);

        // Formato
        $mail->isHTML(true);                        // Establecer formato de correo a HTML
        $mail->CharSet = 'UTF-8';                   // Establecer codificación de caracteres

        // Registrar configuración exitosa
        error_log("PHPMailer configurado correctamente con servidor: {$env['MAIL']['HOST']}:{$env['MAIL']['PORT']}");

        return $mail;
    } catch (Exception $e) {
        error_log("Error al configurar PHPMailer: " . $e->getMessage());
        return false;
    }
}

/**
 * sendMail
 * Envía un correo electrónico al usuario
 *
 * @param string $email Correo electrónico del destinatario
 * @param string $nombre Nombre del destinatario
 * @param string $subject Asunto del correo
 * @param string $body Cuerpo del correo en formato HTML
 * @param string $altBody Cuerpo alternativo del correo en texto plano
 * @param int $debugLevel Nivel de depuración para PHPMailer (0-4)
 * @param mixed $debugCallback Función de callback para la salida de depuración
 * @return bool True si el correo se envió correctamente, False en caso contrario
 */
function sendMail($email, $nombre, $subject, $body, $altBody, $debugLevel = 0, $debugCallback = null) {
    // Validar el correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Error: Formato de correo electrónico inválido: $email");
        return false;
    }

    // Configurar PHPMailer con el nivel de depuración especificado
    $mail = configurarPHPMailer($debugLevel, $debugCallback);

    if (!$mail) {
        error_log("Error: No se pudo configurar PHPMailer");
        return false;
    }

    try {
        // Destinatario
        $mail->addAddress($email, $nombre);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;

        // Registrar intento de envío
        error_log("Intentando enviar correo a: $email con asunto: $subject");

        // Enviar el correo
        if (!$mail->send()) {
            error_log("Error al enviar correo: " . $mail->ErrorInfo);
            return false;
        }

        // Registrar envío exitoso
        error_log("Correo enviado correctamente a: $email");
        return true;
    } catch (Exception $e) {
        error_log("Excepción al enviar correo a $email: " . $e->getMessage());
        if (isset($mail->ErrorInfo) && !empty($mail->ErrorInfo)) {
            error_log("Detalles del error de PHPMailer: " . $mail->ErrorInfo);
        }
        return false;
    }
}
