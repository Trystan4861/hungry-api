<?php
$valores=array($data["id_supermercado"], $data["order"]);
if (in_array(null,$valores,true))
{
  $json["data"]=$data;
  $json["error_msg"] = $msgerrors["hidden_markets_error"];
}
else
{
  $supermercados->updateSupermercadoOrder($data["id_supermercado"],$data["order"]);
  if (!$supermercados->getLastResult())
  {
    $json["error_msg"] = $supermercados->getErrorMsg();
  }
}
