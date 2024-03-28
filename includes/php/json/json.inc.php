<?php
  use JWT;
  //funcion generate_token a partir de un payload usando JWT::encode($payload,$key)
  function generate_token($data){
    return JWT::encode($data["pass"].$data["microtime"], $_ENV["SECRET_KEY"]);
  }
  
