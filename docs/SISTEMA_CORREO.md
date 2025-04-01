# Sistema de Correo Electrónico

Este documento detalla el sistema de envío de correos electrónicos implementado en la API Hungry, incluyendo la configuración, las funciones utilizadas y los tipos de correos que se envían.

## Tecnología Utilizada

La API Hungry utiliza PHPMailer para el envío de correos electrónicos. PHPMailer es una biblioteca de código abierto para PHP que facilita el envío de correos electrónicos con características como:

- Soporte para SMTP
- Correos HTML y texto plano
- Archivos adjuntos
- Soporte para TLS/SSL
- Codificación UTF-8

## Configuración

La configuración del sistema de correo se realiza a través del archivo `.env` en la sección `[MAIL]` y `[APP]`:

```ini
[MAIL]
USER="your_smtp_email"
PASS="your_smtp_password"
HOST="smtp.gmail.com"
PORT="587"
SECURE="tls"

[APP]
NAME="Hungry by @trystan4861"
API_URL="https://your-api-url.com/api/"
EMAIL_USER="your_app_email"
EMAIL_PASS="your_app_email_password"
```

## Implementación

### Configuración de PHPMailer

La función `configurarPHPMailer` se encarga de configurar una instancia de PHPMailer con los datos del archivo `.env`:

```php
function configurarPHPMailer($mail) {
    global $env;

    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host = $env['MAIL']['HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $env['MAIL']['USER'];
    $mail->Password = $env['MAIL']['PASS'];

    // Configuración de seguridad
    if (isset($env['MAIL']['SECURE']) && strtolower($env['MAIL']['SECURE']) === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }

    $mail->Port = $env['MAIL']['PORT'];

    // Configuración del remitente
    $mail->setFrom($env['APP']['EMAIL_USER'], $env['APP']['NAME']);

    // Configuración adicional
    $mail->CharSet = 'UTF-8';

    return $mail;
}
```

### Envío de Correos

La función `sendMail` se encarga de enviar correos electrónicos utilizando PHPMailer:

```php
function sendMail($to, $toName, $subject, $htmlMessage, $textMessage = '') {
    try {
        // Crear una instancia de PHPMailer
        $mail = new PHPMailer(true);

        // Configurar PHPMailer
        $mail = configurarPHPMailer($mail);

        // Destinatario
        $mail->addAddress($to, $toName);

        // Asunto
        $mail->Subject = $subject;

        // Contenido
        $mail->isHTML(true);
        $mail->Body = $htmlMessage;

        // Mensaje alternativo en texto plano
        if (!empty($textMessage)) {
            $mail->AltBody = $textMessage;
        }

        // Enviar el correo
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo a $to: " . $e->getMessage());
        return false;
    }
}
```

## Tipos de Correos

### Correo de Verificación

El correo de verificación se envía cuando un usuario se registra o solicita un nuevo enlace de verificación. Este correo contiene un enlace con un token único que permite al usuario verificar su dirección de correo electrónico.

La función `sendValidationEmail` de la clase `Usuario` se encarga de enviar este correo:

```php
public function sendValidationEmail($email, $token){
    $subject = "Valida tu cuenta en ".APP_NAME;
    $verification_link = APP_URL . "verifyMail?mail=" . urlencode($email) . "&verify_key=" . $token;

    // Crear mensaje en formato HTML
    $htmlMessage = "
    <!DOCTYPE html>
    <html lang='es'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Verificación de cuenta</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background-color: #f9f9f9;
                border-radius: 5px;
                padding: 20px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .content {
                background-color: #fff;
                border-radius: 5px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .button {
                display: inline-block;
                background-color: lightgreen;
                color: white !important;
                font-weight: bold;
                text-decoration: none;
                padding: 10px 20px;
                border-radius: 5px;
                margin-top: 20px;
            }
            .footer {
                text-align: center;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>".APP_NAME."</h1>
            </div>
            <div class='content'>
                <h2>Verificación de cuenta</h2>
                <p>Gracias por registrarte en ".APP_NAME.".</p>
                <p>Para validar tu cuenta, haz clic en el siguiente botón:</p>
                <p style='text-align: center;'>
                    <a href='".$verification_link."' class='button'>Verificar mi cuenta</a>
                </p>
                <p>O copia y pega el siguiente enlace en tu navegador:</p>
                <p>".$verification_link."</p>
            </div>
            <div class='footer'>
                <p>Este correo fue enviado automáticamente. Por favor, no respondas a este mensaje.</p>
            </div>
        </div>
    </body>
    </html>";

    // Mensaje de texto plano como alternativa
    $textMessage = "Para validar tu cuenta en ".APP_NAME." haz click en el siguiente enlace:\n\n".$verification_link;

    try {
        // Intentamos enviar el correo
        $result = sendMail($email, isset($this->user["nombre"]) ? $this->user["nombre"] : "Usuario", $subject, $htmlMessage, $textMessage);

        // Registramos el resultado
        if ($result) {
            error_log("Correo de verificación enviado correctamente a: $email");
            return true;
        } else {
            error_log("Error al enviar correo de verificación a: $email");
            $this->log["error"] = "Error al enviar correo de verificación";
            return false;
        }
    }
    catch(Exception $e){
        error_log("Excepción al enviar correo de verificación a $email: ".$e->getMessage());
        $this->log["error"] = "Error enviando email de validación: ".$e->getMessage();
        return false;
    }
}
```

## Herramientas de Prueba

La API incluye scripts de prueba para verificar el funcionamiento del sistema de correo:

### test_mail_advanced.php

Este script permite probar diferentes configuraciones SMTP para identificar problemas en el envío de correos electrónicos. Proporciona una interfaz web con tres opciones:

1. **Configuración actual (.env)**: Utiliza la configuración del archivo `.env`.
2. **Configuración alternativa (Gmail)**: Utiliza una configuración predefinida para Gmail.
3. **Configuración personalizada**: Permite al usuario especificar todos los parámetros de configuración.

### test_smtp_connection.php

Este script prueba directamente la conexión SMTP con el servidor configurado y muestra información detallada sobre el proceso. Proporciona información sobre:

- Conexión al servidor SMTP
- Autenticación
- Seguridad TLS/SSL
- Información de OpenSSL
- Configuración de PHP

## Solución de Problemas

### Problemas Comunes

1. **Correos no enviados**:
   - Verificar las credenciales SMTP en el archivo `.env`.
   - Comprobar que el servidor SMTP esté configurado correctamente.
   - Verificar que el puerto SMTP no esté bloqueado por un firewall.

2. **Correos marcados como spam**:
   - Configurar correctamente el remitente (FROM).
   - Utilizar un dominio verificado para el remitente.
   - Incluir texto alternativo en formato plano.

3. **Errores de autenticación**:
   - Verificar las credenciales SMTP.
   - Para Gmail, habilitar el "Acceso de aplicaciones menos seguras" o usar una "Contraseña de aplicación".

4. **Errores de conexión**:
   - Verificar que el servidor SMTP esté disponible.
   - Comprobar la configuración de seguridad (TLS/SSL).
   - Verificar que el puerto SMTP sea correcto.

### Registro de Errores

Los errores relacionados con el envío de correos se registran utilizando `error_log()`. Para solucionar problemas, revisar los logs de error de PHP.

## Mejoras Futuras

1. **Plantillas de Correo**: Implementar un sistema de plantillas para facilitar la creación y mantenimiento de los correos.

2. **Cola de Correos**: Implementar un sistema de cola para el envío de correos en segundo plano.

3. **Seguimiento de Correos**: Añadir funcionalidad para rastrear si los correos han sido abiertos o si se ha hecho clic en los enlaces.

4. **Múltiples Proveedores**: Implementar soporte para múltiples proveedores de correo (Mailgun, SendGrid, etc.) para mayor fiabilidad.

5. **Límites de Envío**: Implementar límites de envío para prevenir el abuso del sistema.

## Ejemplo de Uso

### Envío de un Correo Simple

```php
$to = "usuario@ejemplo.com";
$toName = "Usuario Ejemplo";
$subject = "Asunto del correo";
$htmlMessage = "<h1>Hola</h1><p>Este es un correo de prueba.</p>";
$textMessage = "Hola. Este es un correo de prueba.";

$result = sendMail($to, $toName, $subject, $htmlMessage, $textMessage);

if ($result) {
    echo "Correo enviado correctamente";
} else {
    echo "Error al enviar el correo";
}
```

### Envío de un Correo de Verificación

```php
$usuario = new Usuario();
$email = "usuario@ejemplo.com";
$token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...";

$result = $usuario->sendValidationEmail($email, $token);

if ($result) {
    echo "Correo de verificación enviado correctamente";
} else {
    echo "Error al enviar el correo de verificación";
}
```
