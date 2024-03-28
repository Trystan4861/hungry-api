<?php
  if ($user->isLoaded())
  {
    $usuario=$user->getUser();
    if (!$usuario["verified"])
    {
      $user->verifyUser();
      $json["result"]=true;
      $json["message"]="Cuenta verificada";
    }
    else
    {
      $json["result"]=false;
      $json["message"]="Cuenta ya verificada";
    }
  }
  else
  {
    $json["result"]=false;
    $json["message"]="Enlace de verificación no válido";
  }