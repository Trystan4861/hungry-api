<?php
  if (!isset($data['email']) || !isset($data["pass"]))
  {
    $json["error_msg"] = $msgerrors["login_error"];
  }
  else {
    $sql="SELECT * FROM usuarios WHERE email=:email and pass=:pass";
    $consulta=$DAO->prepare($sql);
    $consulta->bindValue(":email",$data["email"]);
    $consulta->bindValue(":pass", $data["pass"]);
    $consulta->execute();
    $resultado=$consulta->fetch(PDO::FETCH_ASSOC);
    if ($resultado)
    {
      $id_usuario = $resultado["id"];
      $json["token"] = $resultado["token"];
      $token = generate_token($resultado["pass"]);
      if ($resultado["token"]!=$token)
      {
        $sql="UPDATE usuarios SET token=:token WHERE id=:id";
        $consulta=$DAO->prepare($sql);
        $consulta->bindValue(":token", $token);
        $consulta->bindValue(":id", $id_usuario);
        $consulta->execute();
        $json["token"] = $token;
      }
    }
    else {
      $json["error_msg"] = $msgerrors["user_error"];
    }
  }