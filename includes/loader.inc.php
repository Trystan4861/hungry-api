<?php 
  $FTO=array(
    "config"              =>  "includes/config/config.inc.php",
    "config.db"           =>  "includes/config/config.db.inc.php",
    "user.class"          =>  "includes/clases/user.class.inc.php",
    "categorias.class"    =>  "includes/clases/categorias.class.inc.php",
    "productos.class"     =>  "includes/clases/productos.class.inc.php",
    "supermercados.class" =>  "includes/clases/supermercados.class.inc.php",
    "json"                =>  "includes/php/json/json.inc.php",
    "jwt"                 =>  "includes/php/jwt/jwt.inc.php",
    "server"              =>  "includes/php/server/server.inc.php",
  );

  foreach($FTO as $key=>$value){
    if (file_exists(ROOT.$value))
    {
      require_once ROOT.$value;
    }
    else
    {
      $json["error_msg"]="Error «$value» no existe";
    }
  }
