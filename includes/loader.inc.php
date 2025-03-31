<?php
  $env=parse_ini_file(ROOT.'.env');

  $FTO=array(
    "config"              =>  "includes/config/config.inc.php",
    "config.utf8"         =>  "includes/config/config.utf8.inc.php",
    "config.db"           =>  "includes/config/config.db.inc.php",
    "usuario.class"       =>  "includes/clases/usuario.class.inc.php",
    "categorias.class"    =>  "includes/clases/categorias.class.inc.php",
    "productos.class"     =>  "includes/clases/productos.class.inc.php",
    "supermercados.class" =>  "includes/clases/supermercados.class.inc.php",
    "json"                =>  "includes/php/json/json.inc.php",
    "jwt"                 =>  "includes/php/jwt/jwt.inc.php",
    "server"              =>  "includes/php/server/server.inc.php",
    "functions"           =>  "includes/php/functions.php",
    "json_handler"        =>  "includes/php/json_handler.php",
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
  // Crear la tabla usuarios_devices si no existe
  if (!isset($json["error_msg"]) && file_exists(ROOT."includes/actions/post/createUsersDevicesTable.inc.php")) {
    require_once ROOT."includes/actions/post/createUsersDevicesTable.inc.php";
  }
