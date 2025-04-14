<?php
  if (checkNullValues($data,"id_producto","text"))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {
    $json["producto"]=$productos->updateProductoText($data["id_producto"],$data["text"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }