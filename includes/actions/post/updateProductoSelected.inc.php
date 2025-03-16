<?php
  if (checkNullValues($data,"id_producto","selected")) 
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {

    $json["producto"]=$productos->updateProductoSelected($data["id_producto"],$data["selected"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }