<?php
  $valores=array($data["id_categoria"], $data["visible"]);
  if (in_array(null,$valores,true))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_cagegory_error"];
  }
  else
  {

    $categorias->updateCategoriaVisible($data["id_categoria"],$data["visible"]);
    if (!$categorias->getLastResult())
    {
      $json["error_msg"] = $categorias->getErrorMsg();
    }
  }