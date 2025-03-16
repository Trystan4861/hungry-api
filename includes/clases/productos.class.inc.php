<?php
/*namespace Hungry\Clases;
use \PDO;
use \PDOException;*/
class Productos {
    
    private $DAO;
    private $userId;
    private $is_done;
    private $error_msg;
    private $producto;

    public function __construct($userId) {
        global $DAO;
        $this->DAO = $DAO;
        $this->userId = $userId;
    }

    private function returnError($error_msg) {
        $this->is_done = false;
        $this->error_msg = $error_msg;
        return null;
    }
    private function setResult($result)
    {
        if (!is_array($result[0]))
        {
            $this->producto=$result;
        }
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

    public function getProductos() {
        try
        {
            $consulta = $this->DAO->prepare("SELECT `id`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done` FROM productos where `fk_id_usuario`=:userId");
            $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
        }
        catch (PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }

    public function loadProducto($idProducto) {
        $consulta = $this->DAO->prepare("SELECT `id`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done` FROM productos where `id`=:idProducto and `fk_id_usuario`=:userId");
        $consulta->bindValue(":idProducto", $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->setResult($consulta->fetch(PDO::FETCH_ASSOC));
    }


    public function getProductosPorCategoria($idCategoria) {
        $consulta = $this->DAO->prepare("SELECT `id`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done` FROM productos where `fk_categoria`=:idCategoria and `fk_id_usuario`=:userId");
        $consulta->bindValue(":idCategoria", $idCategoria, PDO::PARAM_INT);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    public function getProductosPorNombre($nombre) {
        $consulta = $this->DAO->prepare("SELECT `id`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done` FROM productos where `text` like :nombre and `fk_id_usuario`=:userId");
        $consulta->bindValue(":text", $nombre, PDO::PARAM_STR);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
    }
    public function newProducto($data){
        try
        {
            
            $consulta = $this->DAO->prepare("INSERT INTO productos (`text`, `fk_id_categoria`, `fk_id_usuario`, `fk_id_supermercado`) VALUES (:text, :fk_categoria, :fk_id_usuario, :fk_id_supermercado)");
            $consulta->bindValue(":text", $data['text'], PDO::PARAM_STR);
            $consulta->bindValue(":fk_categoria", $data['id_categoria'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_supermercado", $data['id_supermercado'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadProducto($this->DAO->lastInsertId());
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function updateProductoAmount($idProducto, $amount){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `amount`=:amount WHERE `id`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":amount", $amount, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadProducto($idProducto);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function updateProductoSelected($idProducto, $selected){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `selected`=:selected WHERE `id`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":selected", $selected, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadProducto($idProducto);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function updateProductoDone($idProducto, $done){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `done`=:done WHERE `id`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":done", $done, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadProducto($idProducto);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function updateProducto($data){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `text`=:text, `fk_id_categoria`=:fk_id_categoria, `fk_id_supermercado`=:fk_id_supermercado WHERE `id`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":text", $data['text'], PDO::PARAM_STR);
            $consulta->bindValue(":fk_id_categoria", $data['id_categoria'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_supermercado", $data['id_supermercado'], PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $data['id_producto'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->setResult($data);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function deleteProducto($idProducto){
        try
        {
            $consulta = $this->DAO->prepare("DELETE FROM productos WHERE `id`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $this->is_done=true;
            return true;
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }

    public function deleteProductosNotIn($data){
        try
        {
            $ids=implode(",",array_column($data, 'id_producto'));
            $consulta = $this->DAO->prepare("DELETE FROM productos WHERE `id` NOT IN ($ids) and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $this->is_done=true;
            return true;
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }

    public function getErrorMsg(){
        return $this->error_msg;
    }
    public function getLastResult(){
        return $this->is_done;
    }
    public function getProducto(){
        return $this->producto;
    }

}