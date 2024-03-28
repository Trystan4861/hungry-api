<?php
  $valores=array($data["id_producto"], $data["amount"]);
  if (in_array(null,$valores,true))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {

    $productos->updateProductoAmount($data["id_producto"],$data["amount"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }