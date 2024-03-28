<?php
  $valores=array($data["id_categoria"], $data["id_supermercado"], $data["text"]);
  if (in_array(null,$valores,true))
  {
    $json["error_msg"]=$msgerrors["new_product_error"];
  }
  else{
    $productos->newProducto($data);
    if (!$productos->getLastResult())
    {
      $json["error_msg"]=$productos->getErrorMsg();
    }
    else
    {
      $producto=$productos->getProducto();
      $json["id_producto"]=$producto["id_producto"];
    }
  }
