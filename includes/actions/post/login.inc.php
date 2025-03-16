<?php
  $valores=array($data["email"], $data["pass"]);
  if (in_array(null,$valores,true))
  {
    $json["error_msg"] = $msgerrors["login_error"];
  }
  else {
    // Cargamos el usuario
    $user= new User($data);
    // si el usuario existe
    if ($user->isLoaded())
    {
      // obtenemos el usuario y su token
      $resultado=$user->getUser();
      // si el usuario esta verificado o no es necesario validarlo
      if ($resultado["verified"] || !MUST_VALIDATE)
      {
        $id_usuario = $resultado["id"];
        $json["device"]=$user->getDevice();
        $json["token"] = $resultado["token"];

        //regeneramos el token del usuario para hacer las comprobaciones pertinentes
        $token = generate_token($resultado);
        if ($resultado["token"]!=$token)
        {
          // si el token no es el mismo que el guardado, lo actualizamos en la base de datos
          $user->updateToken($token);
          $json["token"] = $token;
        }
      }
      else {
          // si el usuario no esta verificado, mandamos un error
          $json["error_msg"] = $msgerrors["verified_error"];
      }
    }
    else {
      // si los datos de acceso son incorrecots, mandamos un error
      if ($user->emailExists(trim(strtolower($data["email"]))))
      {
        $json["error_msg"] = $msgerrors["user_error"];
      }
      else{
        $json["error_msg"] = $msgerrors["no_mail_error"];
      }
    }
  }