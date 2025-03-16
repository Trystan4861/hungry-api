<?php
  if (in_array(null,array($data["id_producto"], $data["done"]),true))
  {
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {

    $json["producto"]=$productos->updateProductoDone($data["id_producto"],$data["done"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
  }