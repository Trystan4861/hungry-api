<?php

class Productos {
    
    public function getByUserId($userId) {
        global $DAO;
        $consulta = $DAO->prepare("SELECT * FROM productos WHERE fk_id_usuario = :userId");
        $consulta->bindParam(":userId", $userId, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}