<?php
  if (!$data["id_producto"])
  {
    $json["error_msg"]=$msgerrors["delete_product_error"];
  }
  else{
    $productos->deleteProducto($data["id_producto"]);
    if (!$productos->getLastResult())
    {
      $json["error_msg"]=$productos->getErrorMsg();
    }
    else
    {
      $producto=$productos->getProducto();
      $json["data"]="Producto eliminado correctamente";
    }
  }
