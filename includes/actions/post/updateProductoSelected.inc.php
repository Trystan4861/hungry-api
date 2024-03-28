<?php
  $valores=array($data["id_producto"], $data["selected"]);
  if (in_array(null,$valores,true))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {

    $productos->updateProductoSelected($data["id_producto"],$data["selected"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }