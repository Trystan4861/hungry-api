<?php
if (checkNullValues($data,"id_producto","text","id_categoria","id_supermercado")){
    $json["data"] = $data;
    $json["error_msg"] = $msgerrors["update_product_error"];
} else {
  $productos->updateProducto($data);
  if (!$productos->getLastResult()) {
      $json["error_msg"] = $productos->getErrorMsg();
  }
  else{
    $updates = [
        "amount" => "updateProductoAmount",
        "selected" => "updateProductoSelected",
        "done" => "updateProductoDone"
    ];
    foreach ($updates as $key => $method) {
      if (isset($data[$key])) {
        $productos->$method($data["id_producto"], $data[$key]);
        if (!$productos->getLastResult()) {
          $json["error_msg"] = $productos->getErrorMsg();
          break;
        }
      }
    }
  }
}
?>