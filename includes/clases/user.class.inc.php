<?php
class User {
    private $is_done;
    private $user;
    private $error_msg;
    private $DAO;
    
    //funcion __construct que si recibe los datos de acceso tratará de cargar el usuario desde la base de datos
    public function __construct($data = null) {
        global $DAO;
        $this->DAO = $DAO;
        if ($data) {
            $this->load($data);
        } else {
            $this->emptyUser();
        }
    }
    private function returnError($error_msg) {
        $this->is_done = false;
        $this->error_msg = $error_msg;
        return null;
    }

    //funcion que comprueba si el correo electrónico existe en la base de datos
    public function emailExists($email) {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = :email";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":email", $email);
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                return true;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }
    
    //funcion que limpia el usuario
    public function emptyUser() {
        $this->is_done = false;
        $this->user = null;
        $this->error_msg = null;
    }

    //funcion que carga el usuario desde la base de datos con los datos de acceso
    public function load($data) {
        try {
            if (is_array($data)) {
                $sql = "SELECT * FROM usuarios WHERE email = :email AND pass = :pass";
                $consulta = $this->DAO->prepare($sql);
                $consulta->bindValue(":email", $data["email"]);
                $consulta->bindValue(":pass", $data["pass"]);
            } else {
                $sql = "SELECT * FROM usuarios WHERE token = :token";
                $consulta = $this->DAO->prepare($sql);
                $consulta->bindValue(":token", $data);
            }
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                $this->user = $usuario;
                $this->is_done = true;
                return $this->user['id'];
            } else {
                $this->is_done = false;
                return null;
            }
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    //funcion que actualiza el token del usuario en la base de datos
    public function updateToken($new_token){
        try {
            $sql = "UPDATE usuarios SET token = :token WHERE id = :id";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":token", $new_token);
            $consulta->bindValue(":id", $this->user["id"]);
            $consulta->execute();
            return $this->load($this->user["token"]);
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    //funcion que crea un nuevo usuario en la base de datos con los datos de acceso y el token generado
    public function createUser($data) {
        $data["microtime"]=microtime(true);
        $token=generate_token($data);
        try {
            $sql = "INSERT INTO usuarios (email, pass, token, microtime) VALUES (:email, :pass, :token,:microtime)";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":email", $data["email"]);
            $consulta->bindValue(":pass", $data["pass"]);
            $consulta->bindValue(":microtime",$data["microtime"]);
            $consulta->bindValue(":token", $token);
            $consulta->execute();
            return $this->load($this->user["token"]);
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    //funcion para actualizar la contraseña del usuario en la base de datos y generar el token a partir del microtime actual
    public function updatePassword($data){
        $data["microtime"]=microtime(true);
        $token=generate_token($data);
        try {
            $sql = "UPDATE usuarios SET pass = :pass, token = :token, microtime = :microtime WHERE id = :id";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":pass", $data["pass"]);
            $consulta->bindValue(":microtime", $data["microtime"]);
            $consulta->bindValue(":token", $token);
            $consulta->bindValue(":id", $this->user["id"]);
            $consulta->execute();
            return $this->load($this->user["token"]);
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    //funcion que marca al usuario como verificado en la base de datos
    public function verifyUser(){
        try
        {
            $consulta=$this->DAO->prepare("UPDATE usuarios SET verified = 1 WHERE id = :id");
            $consulta->bindValue(":id", $this->user["id"]);
            $consulta->execute();
            return $this->load($this->user["token"]);
        }
        catch (PDOException $e)
        {
            return $this->returnError($e->getMessage());
        }
    }
    public function setSupermercadosOcultos($data){
        if (is_array($data["supermercados_ocultos"]))
            $data["supermercados_ocultos"] = implode(",", $data["supermercados_ocultos"]);
        else if ($data["supermercados_ocultos"]==null)
            $data["supermercados_ocultos"]="-1";
        $consulta=$this->DAO->prepare("UPDATE usuarios SET supermercados_ocultos = :supermercados_ocultos WHERE id = :id");
        $consulta->bindValue(":supermercados_ocultos", $data["supermercados_ocultos"]);
        $consulta->bindValue(":id", $this->user["id"]);
        $consulta->execute();
        return $this->load($this->user["token"]);
    }

    public function getLastResult() {
        return $this->is_done;
    }

    public function getIdUsuario() {
        return $this->user["id"];
    }

    public function getUser() {
        return $this->user;
    }
    public function isLoaded(){
        return $this->user!=null;
    }
    public function getErrorMsg() {
        return $this->error_msg;
    }       
    public function getSupermercadosOcultos(){
        return $this->user["supermercados_ocultos"];
    }
}
