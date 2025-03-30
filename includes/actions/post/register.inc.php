<?php
  // Verificamos si los datos necesarios están presentes
  if (checkNullValues($data, "email", "pass")) {
    // Si falta algún dato, mostramos un mensaje de error
    $json["error_msg"] = "Faltan datos para el registro. Se requiere email y contraseña.";
  }
  // Verificamos si los datos están vacíos
  else if (empty($data["email"]) || empty($data["pass"])) {
    $json["error_msg"] = "Los campos de registro no pueden estar vacíos.";
  }
  // Validamos el formato del email
  else if (!validate_email($data["email"])) {
    $json["error_msg"] = "El formato del correo electrónico no es válido.";
  }
  // Validamos la longitud de la contraseña
  else if (!validate_password($data["pass"])) {
    $json["error_msg"] = "La contraseña debe tener al menos 6 caracteres.";
  }
  else {
    $user = new Usuario();
    if ($user->emailExists($data["email"])) {
      $json["error_msg"] = $msgerrors["email_error"];
    }
    else {
      $data["microtime"] = microtime(true);
      $token = generate_token($data);
      $user->createUser($data);

      if (!$user->getLastResult()) {
        $json["error_msg"] = $msgerrors["register_error"];
      }
      else {
        if (MUST_VALIDATE) {
          $user->sendValidationEmail($data["email"], $token);
          $json["error_msg"] = $msgerrors["register_validate"];
        }
        else {
          $user->validateUser($data["email"], $token);
          $json["token"] = $token;
        }
      }
    }
  }