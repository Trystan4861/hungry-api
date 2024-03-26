<?php
  use JWT;
  //funcion generate_token a partir de un payload usando JWT::encode($payload,$key)
  function generate_token($payload){
    return JWT::encode($payload, $_ENV["SECRET_KEY"]);
  }
  
