<?php
/**
 * verifyMail.inc.php
 * Endpoint para verificar correos electrónicos de usuarios
 *
 * Parámetros:
 * - mail: Correo electrónico a verificar (obligatorio)
 * - verify_key: Clave de verificación (opcional)
 *
 * Comportamiento:
 * - Si no se proporciona verify_key: genera una clave y envía un correo de verificación
 * - Si se proporciona verify_key: verifica el correo usando la clave proporcionada
 */

// Función para mostrar una página HTML con un mensaje
function mostrarPaginaHTML($titulo, $mensaje, $tipo = 'success') {
    // Desactivamos la salida JSON
    global $json_output;
    $json_output = false;

    // Definimos colores según el tipo de mensaje
    $colorFondo = ($tipo == 'success') ? '#d4edda' : '#f8d7da';
    $colorTexto = ($tipo == 'success') ? '#155724' : '#721c24';
    $colorBorde = ($tipo == 'success') ? '#c3e6cb' : '#f5c6cb';
    $icono = ($tipo == 'success') ? '✓' : '✗';

    // Mostramos la página HTML
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . htmlspecialchars($titulo) . ' - ' . APP_NAME . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                background-color: #f8f9fa;
                margin: 0;
                padding: 20px;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
            }
            .container {
                max-width: 600px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                padding: 30px;
                text-align: center;
            }
            .header {
                margin-bottom: 20px;
            }
            .message-box {
                background-color: ' . $colorFondo . ';
                color: ' . $colorTexto . ';
                border: 1px solid ' . $colorBorde . ';
                border-radius: 5px;
                padding: 15px;
                margin-bottom: 20px;
                text-align: center;
            }
            .icon {
                font-size: 48px;
                margin-bottom: 15px;
            }
            .button {
                display: inline-block;
                background-color: #007bff;
                color: white;
                text-decoration: none;
                padding: 10px 20px;
                border-radius: 5px;
                margin-top: 20px;
                transition: background-color 0.3s;
            }
            .button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>' . APP_NAME . '</h1>
            </div>
            <div class="message-box">
                <div class="icon">' . $icono . '</div>
                <h2>' . htmlspecialchars($titulo) . '</h2>
                <p>' . htmlspecialchars($mensaje) . '</p>
            </div>
        </div>
    </body>
    </html>';
    exit;
}

// Verificamos que se haya proporcionado un correo electrónico
if (!isset($data['mail']) || empty($data['mail'])) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $json["error_msg"] = "Es necesario proporcionar un correo electrónico";
    } else {
        mostrarPaginaHTML("Error de verificación", "Es necesario proporcionar un correo electrónico", "error");
    }
    return;
}

// Validamos el formato del correo electrónico
if (!filter_var($data['mail'], FILTER_VALIDATE_EMAIL)) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $json["error_msg"] = "El formato del correo electrónico no es válido";
    } else {
        mostrarPaginaHTML("Error de verificación", "El formato del correo electrónico no es válido", "error");
    }
    return;
}

// Normalizamos el correo electrónico
$email = strtolower(trim($data['mail']));

// Creamos una instancia de Usuario para trabajar con la base de datos
$usuario = new Usuario();

// Verificamos si el correo existe en la base de datos
if (!$usuario->emailExists($email)) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $json["error_msg"] = $msgerrors["no_mail_error"];
    } else {
        mostrarPaginaHTML("Error de verificación", "El correo electrónico no está registrado en nuestro sistema", "error");
    }
    return;
}

// Obtenemos los datos del usuario
try {
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $consulta = $DAO->prepare($sql);
    $consulta->bindValue(":email", $email);
    $consulta->execute();
    $user_data = $consulta->fetch(PDO::FETCH_ASSOC);

    // Si el usuario ya está verificado
    if ($user_data['verified'] == 1) {
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            $json["result"] = true;
            $json["message"] = "El correo electrónico ya está verificado";
        } else {
            mostrarPaginaHTML("Correo ya verificado", "Tu correo electrónico ya ha sido verificado anteriormente. Puedes iniciar sesión en la aplicación.", "success");
        }
        return;
    }

    // Si se proporciona una clave de verificación
    if (isset($data['verify_key']) && !empty($data['verify_key'])) {
        // Registramos información para depuración
        error_log("Verificando correo: $email con clave: " . $data['verify_key']);
        error_log("Token almacenado en BD: " . $user_data['token']);

        // Verificamos si la clave coincide con el token almacenado
        if ($data['verify_key'] === $user_data['token']) {
            error_log("¡Verificación exitosa! Los tokens coinciden.");

            // Actualizamos el estado de verificación del usuario
            $sql = "UPDATE usuarios SET verified = 1 WHERE id = :id";
            $consulta = $DAO->prepare($sql);
            $consulta->bindValue(":id", $user_data['id']);
            $consulta->execute();

            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $json["result"] = true;
                $json["message"] = "Correo electrónico verificado correctamente";
            } else {
                mostrarPaginaHTML("Verificación exitosa", "¡Tu correo electrónico ha sido verificado correctamente! Ahora puedes iniciar sesión en la aplicación.", "success");
            }
        } else {
            error_log("¡Verificación fallida! Los tokens no coinciden.");
            error_log("Token recibido: " . $data['verify_key']);
            error_log("Token en BD: " . $user_data['token']);

            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $json["result"] = false;
                $json["error_msg"] = $msgerrors["token_error"];
            } else {
                mostrarPaginaHTML("Error de verificación", "El enlace de verificación no es válido o ha expirado. Por favor, solicita un nuevo enlace de verificación.", "error");
            }
        }
    }
    // Si no se proporciona una clave de verificación, generamos una y enviamos el correo
    else {
        // Verificamos si el usuario ya tiene un token
        if (!empty($user_data['token'])) {
            error_log("El usuario ya tiene un token: " . $user_data['token']);
            $stored_token = $user_data['token'];
        } else {
            // Generamos un nuevo token para la verificación
            $microtime = microtime(true);
            $data["microtime"] = $microtime;
            $data["pass"] = $user_data['pass'];

            // Generamos el token y lo guardamos en una variable
            $verify_key = generate_token($data);

            // Registramos información para depuración
            error_log("Generando token de verificación para $email con microtime: $microtime");
            error_log("Token generado: $verify_key");

            // Guardamos el token en la base de datos
            $sql = "UPDATE usuarios SET token = :token, microtime = :microtime WHERE id = :id";
            $consulta = $DAO->prepare($sql);
            $consulta->bindValue(":token", $verify_key);
            $consulta->bindValue(":microtime", $microtime);
            $consulta->bindValue(":id", $user_data['id']);
            $consulta->execute();

            // Verificamos que el token se haya guardado correctamente
            $sql = "SELECT token FROM usuarios WHERE id = :id";
            $consulta = $DAO->prepare($sql);
            $consulta->bindValue(":id", $user_data['id']);
            $consulta->execute();
            $stored_token = $consulta->fetchColumn();

            error_log("Token almacenado en la base de datos: $stored_token");

            // Verificamos que los tokens coincidan
            if ($verify_key !== $stored_token) {
                error_log("¡ADVERTENCIA! El token generado ($verify_key) no coincide con el almacenado ($stored_token)");
            }
        }

        // Preparamos el enlace de verificación usando el token almacenado en la base de datos
        $verification_link = APP_URL . "verifyMail?mail=" . urlencode($email) . "&verify_key=" . $stored_token;

        // Enviamos el correo de verificación usando la función existente
        try {
            // Creamos una instancia de Usuario para enviar el correo
            $user_obj = new Usuario();

            // Enviamos el correo de verificación usando el token almacenado
            $result = $user_obj->sendValidationEmail($email, $stored_token);

            // Registramos el resultado del envío
            if ($result) {
                error_log("Correo de verificación enviado correctamente a: $email con token: $stored_token");
            } else {
                error_log("Error al enviar correo de verificación a: $email");
            }

            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $json["result"] = true;
                $json["message"] = "Se ha enviado un correo de verificación a $email";
            } else {
                mostrarPaginaHTML("Correo enviado", "Se ha enviado un correo de verificación a $email. Por favor, revisa tu bandeja de entrada y sigue las instrucciones para completar la verificación.", "success");
            }
        } catch (Exception $e) {
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                $json["result"] = false;
                $json["error_msg"] = "No se pudo enviar el correo de verificación: " . $e->getMessage();
            } else {
                mostrarPaginaHTML("Error al enviar correo", "No se pudo enviar el correo de verificación. Por favor, inténtalo de nuevo más tarde.", "error");
            }
        }
    }
} catch (PDOException $e) {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        $json["result"] = false;
        $json["error_msg"] = "Error al procesar la solicitud: " . $e->getMessage();
    } else {
        mostrarPaginaHTML("Error del servidor", "Ha ocurrido un error al procesar tu solicitud. Por favor, inténtalo de nuevo más tarde.", "error");
    }
}