<?php
/*namespace Hungry\Clases;*/

use function PHPSTORM_META\type;

class Supermercados{
  private $DAO;
  private $error_msg;
  private $is_done;
  private $supermercados;

  function __construct($supermercadosOcultos=null){
    global $DAO;
    $this->DAO = $DAO;
    if ($supermercadosOcultos!=null){
      $this->loadSupermercados($supermercadosOcultos);
    }
  }
  
  private function returnError($error_msg) {
    $this->is_done = false;
    $this->error_msg = $error_msg;
    return null;
  }
  private function setResult($result)
  {
      $this->supermercados=$result;
      if ($result)
      {
          $this->is_done=true;
      }
      else
      {
          $this->is_done=false;
      }
      return $result;
  }  
  public function loadSupermercados($supermercadosOcultos){
    if (strlen($supermercadosOcultos) == 0 || $supermercadosOcultos=="[]"){
      $sql="SELECT * FROM supermercados";
    }
    else
    {
      if (json_decode($supermercadosOcultos) != null && is_array(json_decode($supermercadosOcultos, true)))
      {
        $supermercadosOcultos = json_decode($supermercadosOcultos, true);
        if (count($supermercadosOcultos) == 0)
        {
          $supermercadosOcultos = array("-1");
        }
        $supermercadosOcultos = implode(", ", $supermercadosOcultos);
      }
      $sql="SELECT * FROM supermercados where id not in ($supermercadosOcultos)";
    }
    try
      {
        $consulta = $this->DAO->query($sql);
        $consulta->execute();
        return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
      }
      catch(PDOException $e)
      {
        return $this->returnError($e->getMessage());
      }
  }
  public function getSupermercados(){
    return $this->supermercados;
  }
  public function getErrorMsg(){
    return $this->error_msg;
  }
  public function getLastResult(){
      return $this->is_done;
  }

}