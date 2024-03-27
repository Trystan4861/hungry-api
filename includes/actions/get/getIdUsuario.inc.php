<?php
  $token=null;
  if (isset($_MySERVER['HTTP_AUTHORIZATION']) && strpos($_MySERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0){
    $token = substr($_MySERVER['HTTP_AUTHORIZATION'], 7);
  }elseif (isset($_MySERVER['HTTP_TOKEN'])) {
    $token=$_MySERVER['HTTP_TOKEN'];
  }elseif (isset($data["token"])) {
    $token=$data["token"];
  }
  if($token==null)
  {
    $json["error_msg"] = $msgerrors["token_error"];
  }
  else
  {
    $user=new User();
    $id_usuario=$user->loadFromToken($token);
    if($id_usuario==null)
    {
      $json["error_msg"] = $msgerrors["token_error"];
    }
    elseif($action=="getIdUsuario")
    {
      $json["id_usuario"]=$id_usuario;
    }
  }
