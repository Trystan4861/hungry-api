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
        if (!isset($result[0]))
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
            $consulta = $this->DAO->prepare("SELECT `id`,`id_producto`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done`,`timestamp` FROM productos where `fk_id_usuario`=:userId");
            $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $result = $consulta->fetchAll(PDO::FETCH_ASSOC);

            // Transformar el resultado para usar id_producto como id
            foreach ($result as &$producto) {
                $producto['id'] = $producto['id_producto'];
                unset($producto['id_producto']);
            }

            return $this->setResult($result);
        }
        catch (PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }

    public function loadProducto($idProducto) {
        $consulta = $this->DAO->prepare("SELECT `id`,`id_producto`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done`,`timestamp` FROM productos where `id`=:idProducto and `fk_id_usuario`=:userId");
        $consulta->bindValue(":idProducto", $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        $result = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Transformar el resultado para usar id_producto como id
            $result['id'] = $result['id_producto'];
            unset($result['id_producto']);
        }

        return $this->setResult($result);
    }


    public function getProductosPorCategoria($idCategoria) {
        $consulta = $this->DAO->prepare("SELECT `id`,`id_producto`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done`,`timestamp` FROM productos where `fk_id_categoria`=:idCategoria and `fk_id_usuario`=:userId");
        $consulta->bindValue(":idCategoria", $idCategoria, PDO::PARAM_INT);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        $result = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Transformar el resultado para usar id_producto como id
        foreach ($result as &$producto) {
            $producto['id'] = $producto['id_producto'];
            unset($producto['id_producto']);
        }

        return $this->setResult($result);
    }

    public function getProductosPorNombre($nombre) {
        $consulta = $this->DAO->prepare("SELECT `id`,`id_producto`,`fk_id_categoria` as 'id_categoria', `fk_id_supermercado` as 'id_supermercado',`text`,`amount`,`selected`,`done`,`timestamp` FROM productos where `text` like :nombre and `fk_id_usuario`=:userId");
        $consulta->bindValue(":nombre", '%'.$nombre.'%', PDO::PARAM_STR);
        $consulta->bindValue(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        $result = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Transformar el resultado para usar id_producto como id
        foreach ($result as &$producto) {
            $producto['id'] = $producto['id_producto'];
            unset($producto['id_producto']);
        }

        return $this->setResult($result);
    }
    /**
     * newProducto
     * Crea un nuevo producto en la base de datos
     * @param array $data Datos del producto a crear
     * @return mixed Producto creado o null en caso de error
     */
    public function newProducto($data){
        try
        {
            // El timestamp se establece automáticamente con el valor por defecto
            $consulta = $this->DAO->prepare("INSERT INTO productos (`text`, `id_producto`, `fk_id_categoria`, `fk_id_usuario`, `fk_id_supermercado`) VALUES (:text, :id_producto, :fk_categoria, :fk_id_usuario, :fk_id_supermercado)");
            $consulta->bindValue(":text", $data['text'], PDO::PARAM_STR);
            $consulta->bindValue(":id_producto", $data['id_producto'], PDO::PARAM_STR);
            $consulta->bindValue(":fk_categoria", $data['fk_id_categoria'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_supermercado", $data['fk_id_supermercado'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadProducto($this->DAO->lastInsertId());
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    /**
     * updateProductoAmount
     * Actualiza la cantidad de un producto
     * @param int $idProducto ID del producto a actualizar
     * @param int $amount Nueva cantidad
     * @return mixed Producto actualizado o null en caso de error
     */
    public function updateProductoAmount($idProducto, $amount){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `amount`=:amount WHERE `id_producto`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":amount", $amount, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();

            // Obtener el id interno del producto actualizado
            $consulta = $this->DAO->prepare("SELECT `id` FROM productos WHERE `id_producto`=:id_producto AND `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $result = $consulta->fetch(PDO::FETCH_ASSOC);

            return $this->loadProducto($result['id']);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    /**
     * updateProductoSelected
     * Actualiza el estado de selección de un producto
     * @param int $idProducto ID del producto a actualizar
     * @param int $selected Estado de selección (1 o 0)
     * @return mixed Producto actualizado o null en caso de error
     */
    public function updateProductoSelected($idProducto, $selected){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `selected`=:selected WHERE `id_producto`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":selected", $selected, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();

            // Obtener el id interno del producto actualizado
            $consulta = $this->DAO->prepare("SELECT `id` FROM productos WHERE `id_producto`=:id_producto AND `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $result = $consulta->fetch(PDO::FETCH_ASSOC);

            return $this->loadProducto($result['id']);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    /**
     * updateProductoDone
     * Actualiza el estado de completado de un producto
     * @param int $idProducto ID del producto a actualizar
     * @param int $done Estado de completado (1 o 0)
     * @return mixed Producto actualizado o null en caso de error
     */
    public function updateProductoDone($idProducto, $done){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `done`=:done WHERE `id_producto`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":done", $done, PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();

            // Obtener el id interno del producto actualizado
            $consulta = $this->DAO->prepare("SELECT `id` FROM productos WHERE `id_producto`=:id_producto AND `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":id_producto", $idProducto, PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $result = $consulta->fetch(PDO::FETCH_ASSOC);

            return $this->loadProducto($result['id']);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    /**
     * updateProducto
     * Actualiza los datos de un producto
     * @param array $data Datos del producto a actualizar
     * @return mixed Producto actualizado o null en caso de error
     */
    public function updateProducto($data){
        try
        {
            $consulta = $this->DAO->prepare("UPDATE productos SET `text`=:text, `fk_id_categoria`=:fk_id_categoria, `fk_id_supermercado`=:fk_id_supermercado WHERE `id_producto`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":text", $data['text'], PDO::PARAM_STR);
            $consulta->bindValue(":fk_id_categoria", $data['id_categoria'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_supermercado", $data['id_supermercado'], PDO::PARAM_INT);
            $consulta->bindValue(":id_producto", $data['id'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();

            // Obtener el id interno del producto actualizado
            $consulta = $this->DAO->prepare("SELECT `id` FROM productos WHERE `id_producto`=:id_producto AND `fk_id_usuario`=:fk_id_usuario");
            $consulta->bindValue(":id_producto", $data['id'], PDO::PARAM_INT);
            $consulta->bindValue(":fk_id_usuario", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            $result = $consulta->fetch(PDO::FETCH_ASSOC);

            return $this->loadProducto($result['id']);
        }
        catch(PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function deleteProducto($idProducto){
        try
        {
            $consulta = $this->DAO->prepare("DELETE FROM productos WHERE `id_producto`=:id_producto and `fk_id_usuario`=:fk_id_usuario");
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
            if (empty($data)) {
                $this->is_done = true;
                return true;
            }

            $ids = implode(",", array_column($data, 'id'));
            if (empty($ids)) {
                $this->is_done = true;
                return true;
            }

            $consulta = $this->DAO->prepare("DELETE FROM productos WHERE `id_producto` NOT IN ($ids) and `fk_id_usuario`=:fk_id_usuario");
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