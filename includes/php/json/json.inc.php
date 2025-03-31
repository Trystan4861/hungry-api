<?php
  use JWT;
  //funcion generate_token a partir de un payload usando JWT::encode($payload,$key)
  function generate_token($data){
    global $env;
    return JWT::encode($data["pass"].$data["microtime"], $env["SECRET_KEY"]);
  }