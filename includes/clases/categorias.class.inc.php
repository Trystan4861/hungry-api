<?php
/*namespace Hungry\Clases;
use \PDO;
use \PDOException;*/
class Categorias {
    private $DAO;
    private $userId;
    private $is_done;
    private $categoria;
    private $error_msg;

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
            $this->categoria=$result;
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

    public function loadCategoria($idCategoria) {
        $consulta = $this->DAO->prepare("SELECT uc.`fk_id_categoria` as id_categoria, uc.`text`, c.`bgColor`, uc.`visible` FROM categorias as c, usuarios_categorias as uc where uc.`fk_id_categoria`=c.`id` and c.`id`=:idCategoria and uc.`fk_id_usuario`=:userId");
        $consulta->bindParam(":idCategoria", $idCategoria, PDO::PARAM_INT);
        $consulta->bindParam(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->setResult($consulta->fetch(PDO::FETCH_ASSOC));
    }


    public function getCategorias() {
        $consulta = $this->DAO->prepare("SELECT uc.`fk_id_categoria` as id_categoria, uc.`text`, c.`bgColor`, uc.`visible` FROM categorias as c, usuarios_categorias as uc where uc.`fk_id_categoria`=c.`id` and uc.`fk_id_usuario`=:userId");
        $consulta->bindParam(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->setResult($consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    public function updateCategoriaText($idCategoria,$text)
    {
        try
        {
            $consulta = $this->DAO->prepare("UPDATE usuarios_categorias SET `text`=:text WHERE `fk_id_categoria`=:idCategoria and `fk_id_usuario`=:userId");
            $consulta->bindParam(":idCategoria", $idCategoria, PDO::PARAM_INT);
            $consulta->bindParam(":text", $text, PDO::PARAM_STR);
            $consulta->bindParam(":userId", $this->userId, PDO::PARAM_INT);
            $consulta->execute();
            return $this->loadCategoria($idCategoria);
        }
        catch (PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function updateCategoriaVisible($idCategoria,$visible)
    {
        $consulta = $this->DAO->prepare("UPDATE usuarios_categorias SET `visible`=:visible WHERE `fk_id_categoria`=:idCategoria and `fk_id_usuario`=:userId");
        $consulta->bindParam(":idCategoria", $idCategoria, PDO::PARAM_INT);
        $consulta->bindParam(":visible", $visible, PDO::PARAM_INT);
        $consulta->bindParam(":userId", $this->userId, PDO::PARAM_INT);
        $consulta->execute();
        return $this->loadCategoria($idCategoria);
    }

    public function getLastResult()
    {
        return $this->is_done;
    }
    public function getErrorMsg()
    {
        return $this->error_msg;
    }
    public function getCategoria()
    {
        return $this->categoria;
    }
}