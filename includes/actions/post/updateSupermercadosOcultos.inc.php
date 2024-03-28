<?php
  $valores=array($data["supermercados_ocultos"]);
  if (in_array(null,$valores,true))
  {
    $json["error_msg"]=$msgerrors["hidden_markets_error"];
  }
  else{

    $user->setSupermercadosOcultos($data);

    if (!$user->getLastResult())
    {
      $json["error_msg"]=$user->getErrorMsg();
    }
  }
