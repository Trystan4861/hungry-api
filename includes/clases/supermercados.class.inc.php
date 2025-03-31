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
   * @param string $supermercadosOcultos Lista de IDs de supermercados ocultos (para compatibilidad)
   * @param int $id_usuario ID del usuario para filtrar por visibilidad (opcional)
   */
  function __construct($supermercadosOcultos=null, $id_usuario=null){
    global $DAO;
    $this->DAO = $DAO;
    $this->loadSupermercados($supermercadosOcultos, $id_usuario);
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
   * @param string $supermercadosOcultos Lista de IDs de supermercados ocultos (para compatibilidad)
   * @param int $id_usuario ID del usuario para filtrar por visibilidad (opcional)
   * @return array|null Array de supermercados o null en caso de error
   */
  public function loadSupermercados($supermercadosOcultos, $id_usuario = null){
    try {
      // Si tenemos un ID de usuario, usamos la tabla usuarios_supermercados
      if ($id_usuario) {
        $sql = "SELECT s.*, us.timestamp, us.visible, us.order
                FROM supermercados s
                LEFT JOIN usuarios_supermercados us ON s.id = us.fk_id_supermercado AND us.fk_id_usuario = :id_usuario
                WHERE us.visible = 1 OR us.visible IS NULL
                ORDER BY COALESCE(us.order, 999999), s.nombre";
        $consulta = $this->DAO->prepare($sql);
        $consulta->bindValue(":id_usuario", $id_usuario);
        $consulta->execute();
      }
      // Si no tenemos ID de usuario pero tenemos supermercados ocultos (compatibilidad)
      else if ($supermercadosOcultos && strlen($supermercadosOcultos) > 0 && $supermercadosOcultos != "[]" && $supermercadosOcultos != "-1") {
        // Convertimos a array si es JSON
        if (json_decode($supermercadosOcultos) != null && is_array(json_decode($supermercadosOcultos, true))) {
          $supermercadosOcultos = json_decode($supermercadosOcultos, true);
          if (count($supermercadosOcultos) == 0) {
            $supermercadosOcultos = array("-1");
          }
          $supermercadosOcultos = implode(", ", $supermercadosOcultos);
        }

        $sql = "SELECT s.*, us.timestamp
                FROM supermercados s
                LEFT JOIN usuarios_supermercados us ON s.id = us.fk_id_supermercado
                WHERE s.id NOT IN ($supermercadosOcultos)
                ORDER BY s.nombre";
        $consulta = $this->DAO->query($sql);
        $consulta->execute();
      }
      // Si no hay filtros, mostramos todos los supermercados
      else {
        $sql = "SELECT s.*, us.timestamp
                FROM supermercados s
                LEFT JOIN usuarios_supermercados us ON s.id = us.fk_id_supermercado
                ORDER BY s.nombre";
        $consulta = $this->DAO->query($sql);
        $consulta->execute();
      }

      return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
    } catch(PDOException $e) {
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