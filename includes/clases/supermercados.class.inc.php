<?php
/*namespace Hungry\Clases;*/

use function PHPSTORM_META\type;

class Supermercados{
  private $DAO;
  private $error_msg;
  private $is_done;
  private $supermercados;

  /**
   * __construct
   * Constructor de la clase Supermercados
   * @param int $id_usuario ID del usuario para filtrar por visibilidad (opcional)
   */
  function __construct($id_usuario=null){
    global $DAO;
    $this->DAO = $DAO;
    $this->loadSupermercados($id_usuario);
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
  /**
   * loadSupermercados
   * Carga los supermercados según la configuración de visibilidad del usuario
   * @param int $id_usuario ID del usuario para filtrar por visibilidad (opcional)
   * @return array|null Array de supermercados o null en caso de error
   */
  public function loadSupermercados( $id_usuario = null){
    try {
      // Si tenemos un ID de usuario, usamos la tabla usuarios_supermercados
      if ($id_usuario) {
        $sql = "SELECT s.*, us.timestamp, us.visible, us.order
                FROM supermercados s
                LEFT JOIN usuarios_supermercados us ON s.id = us.fk_id_supermercado AND us.fk_id_usuario = :id_usuario
                WHERE us.visible = 1 OR us.visible IS NULL
                ORDER BY COALESCE(us.order, 999999), s.text";
        $consulta = $this->DAO->prepare($sql);
        $consulta->bindValue(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $consulta->execute();
      }
      // Si no hay filtros, mostramos todos los supermercados
      else {
        $sql = "SELECT *
                FROM supermercados
                ORDER BY `text`";
        $consulta = $this->DAO->query($sql);
        $consulta->execute();
      }

      return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
    } catch(PDOException $e) {
      return $this->returnError($e->getMessage());
    }
  }

  public function updateSupermercadoVisible($id_supermercado,$visible){
    try {
      $sql="UPDATE usuarios_supermercados SET visible=:visible WHERE fk_id_supermercado=:id_supermercado and fk_id_usuario=:id_usuario";
      $consulta = $this->DAO->prepare($sql);
      $consulta->bindValue(':id_supermercado',$id_supermercado,PDO::PARAM_INT);
      $consulta->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
      $consulta->bindValue(':visible',intval($visible),PDO::PARAM_BOOL);
      return $this->setResult($consulta->execute());
    }
    catch(PDOException $e) {
      return $this->returnError($e->getMessage());
    }
  }

  public function updateSupermercadoOrder($id_supermercado,$order){
    try {
      $sql="UPDATE usuarios_supermercados SET order=:order WHERE fk_id_supermercado=:id_supermercado and fk_id_usuario=:id_usuario";
      $consulta = $this->DAO->prepare($sql);
      $consulta->bindValue(':id_supermercado',$id_supermercado,PDO::PARAM_INT);
      $consulta->bindValue(':id_usuario',$id_usuario,PDO::PARAM_INT);
      $consulta->bindValue(':order',intval($order),PDO::PARAM_INT);
      return $this->setResult($consulta->execute());
    }
    catch(PDOException $e) {
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