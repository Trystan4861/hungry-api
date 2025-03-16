<?php
  if (checkNullValues($data,"id_producto","amount"))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {
    $json["producto"]=$productos->updateProductoAmount($data["id_producto"],$data["amount"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }