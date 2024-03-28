<?php
  $valores=array($data["id_producto"], $data["text"],$data["id_categoria"],$data["id_supermercado"]);
  if (in_array(null,$valores,true))
  {
    $json["data"]=$data;
    $json["error_msg"] = $msgerrors["update_product_error"];
  }
  else
  {
    $productos->updateProducto($data);
    if (!$productos->getLastResult())
    {
      $json["error_msg"] = $productos->getErrorMsg();
    }
    if (!isset($json["error_msg"]) && isset($data["amount"]))
    {
      //require_once "./updateProductoAmount.inc.php";
      $json["path"]=__DIR__;
      $productos->updateProductoAmount($data["id_producto"], $data["amount"]);
      if (!$productos->getLastResult())
      {
        $json["error_msg"] = $productos->getErrorMsg();
      }
    }
    if (!isset($json["error_msg"]) && isset($data["selected"]))
    {
      $productos->updateProductoSelected($data["id_producto"], $data["selected"]);
      if (!$productos->getLastResult())
      {
        $json["error_msg"] = $productos->getErrorMsg();
      }
    }
    if (!isset($json["error_msg"]) && isset($data["done"]))
    {
      $productos->updateProductoDone($data["id_producto"], $data["done"]);
      if (!$productos->getLastResult())
      {
        $json["error_msg"] = $productos->getErrorMsg();
      }
    }

  }