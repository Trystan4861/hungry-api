<?php
  $token=null;
  if (isset($data["token"])) {
    $token=$data["token"];
  }
  elseif (isset($_MySERVER['HTTP_AUTHORIZATION']) && strpos($_MySERVER['HTTP_AUTHORIZATION'], 'Bearer ') === 0){
    $token = substr($_MySERVER['HTTP_AUTHORIZATION'], 7);
  }elseif (isset($_MySERVER['HTTP_TOKEN'])) {
    $token=$_MySERVER['HTTP_TOKEN'];
  }
  if($token==null)
  {
    $json["error_msg"] = $msgerrors["token_error"];
  }
  else
  {
    // Si tenemos fingerid en los datos, lo pasamos junto con el token
    if(isset($data["fingerid"]))
    {
      $token=["token"=>"$token","fingerid"=>$data["fingerid"]];
    }
    $user = new Usuario($token);
    if ($user->getLastResult())
    {
      $id_usuario=$user->getIdUsuario();
      if($action=="getIdUsuario")
      {
        $json["id_usuario"]=$id_usuario;
      }
    }
    else
    {
      $json["error_msg"] = $msgerrors["token_error"];
    }
  }
