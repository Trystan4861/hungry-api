<?php
  // Verificamos si los datos necesarios están presentes
  try
  {
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
      $json["error_msg"] = "La contraseña debe tener al menos 8 caracteres, un número, una letra minúscula y una mayúscula.";
    }
    else {
      $user = new Usuario();
      if ($user->emailExists($data["email"])) {
        if (MUST_VALIDATE) {
          // Obtenemos el token actual del usuario
          $sql = "SELECT token FROM usuarios WHERE email = :email";
          $consulta = $DAO->prepare($sql);
          $consulta->bindValue(":email", strtolower(trim($data["email"])));
          $consulta->execute();
          $token = $consulta->fetchColumn();

          // Si no hay token, generamos uno nuevo
          if (!$token) {
            $data["microtime"] = microtime(true);
            $token = generate_token($data);

            // Actualizamos el token en la base de datos
            $sql = "UPDATE usuarios SET token = :token, microtime = :microtime WHERE email = :email";
            $consulta = $DAO->prepare($sql);
            $consulta->bindValue(":token", $token);
            $consulta->bindValue(":microtime", $data["microtime"]);
            $consulta->bindValue(":email", strtolower(trim($data["email"])));
            $consulta->execute();
            $json["error_msg"] = $msgerrors["register_validate"];
          }
          else {
            $json["error_msg"] = $msgerrors["register_must_validate"];
          }

          $user->sendValidationEmail($data["email"], $token);
        }
        else {
          $json["error_msg"] = $msgerrors["email_error"];
        }
      }
      else {
        // Creamos el usuario y obtenemos el token generado
        $user_id = $user->createUser($data);
        $token = $user->getToken();

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
  }
  catch(Exception $e){
    $json["error_msg"] = $msgerrors["unknown_error"];
  }