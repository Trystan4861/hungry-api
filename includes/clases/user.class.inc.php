<?php
class User {
    private $id_usuario;
    private $is_loaded;
    private $user;
    private $error_msg;

    public function loadFromToken($token) {
        global $DAO;
        try {
            $sql = "SELECT * FROM usuarios WHERE token = :token";
            $consulta = $DAO->prepare($sql);
            $consulta->bindValue(":token", $token);
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                $this->id_usuario = $usuario['id'];
                $this->user = $usuario;
                $this->is_loaded = true;
                return $this->id_usuario;
            } else {
                $this->is_loaded = false;
            }
        } catch (PDOException $e) {
            $this->error_msg = $e->getMessage();
        }
        return null;
    }

    public function isLoaded() {
        return $this->is_loaded;
    }

    public function getIdUsuario() {
        return $this->id_usuario;
    }

    public function getUser() {
        return $this->user;
    }
        
}
