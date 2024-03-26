<?php
  if (!isset($data['email']) || !isset($data["pass"]))
  {
    $json["error_msg"] = $msgerrors["login_error"];
  }
  else {
    //insertar nuevo usuario en la tabla usuarios con el email y pass y generar un token aleatorio y guardarlo en la tabla usuarios controlando posibles errores 
    $sql="SELECT * FROM usuarios WHERE email=:email";
    $consulta=$DAO->prepare($sql);
    $consulta->bindValue(":email", $data["email"]);
    $consulta->execute();
    $resultado=$consulta->fetch(PDO::FETCH_ASSOC);
    if ($resultado)
    {
      $json["error_msg"] = $msgerrors["email_error"];
    }
    else {
      $token = generate_token($data["pass"]);
      $sql="INSERT INTO usuarios (email, pass, token) VALUES (:email, :pass, :token)";
      $consulta=$DAO->prepare($sql);
      $consulta->bindValue(":email", $data["email"]);
      $consulta->bindValue(":pass", $data["pass"]);
      $consulta->bindValue(":token", $token);
      $consulta->execute();
      $json["token"] = $token;
    }
  }