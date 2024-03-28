<?php
  $valores=array($data["id_categoria"], $data["text"]);
  if (in_array(null,$valores,true))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_cagegory_error"];
  }
  else
  {

    $categorias->updateCategoriaText($data["id_categoria"],$data["text"]);
    if (!$categorias->getLastResult())
    {
      $json["error_msg"] = $categorias->getErrorMsg();
    }
  }