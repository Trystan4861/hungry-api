<?php
  $valores=array($data["email"], $data["pass"]);
  if (in_array(null,$valores,true))
  {
    $json["error_msg"] = $msgerrors["login_error"];
  }
  else {
    $user=new User();
    if ($user->emailExists($data["email"]))
    {
      $json["error_msg"] = $msgerrors["email_error"];
    }
    else {
      $data["microtime"]=microtime(true);
      $token = generate_token($data);
      $user->createUser($data);
      if (!$user->getLastResult())
      {
        $json["error_msg"] = $msgerrors["register_error"];
      }
      else {
        if (MUST_VALIDATE)
        {
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