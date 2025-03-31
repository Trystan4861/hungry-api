<?php
class Usuario {
    private $is_done;
    private $user;
    private $device;
    private $error_msg;
    private $DAO;
    private $log;
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
    /**
     * returnError
     * Establece un mensaje de error y marca la operación como fallida
     * @param string $error_msg Mensaje de error a establecer
     * @return null Siempre devuelve null para indicar error
     */
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
                // Si tenemos email y pass, es un login normal
                if (isset($data["email"]) && isset($data["pass"])) {
                    $sql = "SELECT * FROM usuarios WHERE email = :email AND pass = :pass";
                    $consulta = $this->DAO->prepare($sql);
                    $consulta->bindValue(":email", strtolower(trim($data["email"])));
                    $consulta->bindValue(":pass", $data["pass"]);
                }
                // Si tenemos token, es una carga por token (posiblemente con fingerid)
                else if (isset($data["token"])) {
                    $sql = "SELECT * FROM usuarios WHERE token = `:token`";
                    $consulta = $this->DAO->prepare($sql);
                    $consulta->bindValue(":token", $data["token"]);
                }
            } else {
                // Si es un string, asumimos que es un token
                $sql = "SELECT * FROM usuarios WHERE token = :token";
                $consulta = $this->DAO->prepare($sql);
                $consulta->bindValue(":token", $data, PDO::PARAM_STR);
            }
            $consulta->execute();
            $usuario = $consulta->fetch(PDO::FETCH_ASSOC);
            if ($usuario) {
                $this->device=0;
                if (isset($data["fingerid"]))
                {
                    try
                    {

                        // Ahora procedemos con la consulta normal
                        $sql="SELECT COALESCE(MAX(id), NULL) AS id,
                        COALESCE(MAX(fk_id_usuario), :id) AS fk_id_usuario,
                        COALESCE(MAX(fingerID), NULL) AS fingerID,
                        (SELECT COUNT(*) FROM usuarios_devices WHERE fk_id_usuario = :id) AS total_registros
                        FROM usuarios_devices
                        WHERE fk_id_usuario = :id AND fingerID = :fingerID;";
                        $consulta=$this->DAO->prepare($sql);
                        $consulta->bindValue(":id", $usuario["id"]);
                        $consulta->bindValue(":fingerID", $data["fingerid"]);
                        $consulta->execute();
                        $result=$consulta->fetch(PDO::FETCH_ASSOC);
                        if ($result["id"]==null)
                        {
                            $sql="INSERT INTO usuarios_devices (fk_id_usuario, fingerID, is_master) VALUES (:id, :fingerID, :is_master)";
                            $consulta=$this->DAO->prepare($sql);
                            $consulta->bindValue(":id", $usuario["id"]);
                            $consulta->bindValue(":fingerID", $data["fingerid"]);
                            $consulta->bindValue(":is_master", $result["total_registros"]==0?1:0, PDO::PARAM_BOOL);
                            $consulta->execute();
                            $result["id"]= $this->DAO->lastInsertId();
                        }
                        $this->device = $result["id"];
                    }
                    catch (PDOException $e)
                    {
                        $this->log["error"]=$e->getMessage();
                        error_log("Error en Usuario::load con fingerID: " . $e->getMessage());
                        // No fallamos completamente, solo registramos el error y continuamos
                        $this->device = 0;
                    }
                }
                $this->user = $usuario;
                $this->is_done = true;
                return $this->user['id'];
            } else {
                $this->is_done = false;
                return null;
            }
        } catch (PDOException $e) {
            $this->log["error"]=$e->getMessage();
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * updateToken
     * Actualiza el token del usuario en la base de datos
     * @param string $new_token Nuevo token a establecer
     * @return mixed Usuario actualizado o null en caso de error
     */
    public function updateToken($new_token){
        try {
            $sql = "UPDATE usuarios SET token = :token WHERE id = :id";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":token", $new_token);
            $consulta->bindValue(":id", $this->user["id"]);
            $consulta->execute();
            return $this->load($new_token);
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * createUser
     * Crea un nuevo usuario en la base de datos con los datos de acceso y el token generado
     * @param array $data Array con los datos del usuario (email, pass)
     * @return mixed ID del usuario si se crea correctamente, null en caso de error
     */
    public function createUser($data) {
        // Validamos que los datos necesarios estén presentes
        if (!isset($data["email"]) || !isset($data["pass"])) {
            return $this->returnError("Faltan datos obligatorios para crear el usuario (email y/o contraseña)");
        }

        // Validamos el formato del email
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
            return $this->returnError("El formato del correo electrónico no es válido");
        }

        // Validamos que la contraseña tenga al menos 6 caracteres
        if (strlen($data["pass"]) < 6) {
            return $this->returnError("La contraseña debe tener al menos 6 caracteres");
        }

        $data["microtime"] = microtime(true);
        $token = generate_token($data);

        try {
            $sql = "INSERT INTO usuarios (email, pass, token, microtime)
                    VALUES (:email, :pass, :token, :microtime)";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":email", strtolower(trim($data["email"])));
            $consulta->bindValue(":pass", $data["pass"]);
            $consulta->bindValue(":microtime", $data["microtime"]);
            $consulta->bindValue(":token", $token);
            $consulta->execute();

            // Verificamos si se insertó correctamente
            if ($consulta->rowCount() > 0) {
                return $this->load($token);
            } else {
                return $this->returnError("No se pudo crear el usuario. No se insertó ningún registro.");
            }
        } catch (PDOException $e) {
            // Capturamos errores específicos de la base de datos
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                return $this->returnError("El correo electrónico ya está registrado en el sistema");
            }
            return $this->returnError("Error de base de datos: " . $e->getMessage());
        }
    }

    /**
     * updatePassword
     * Actualiza la contraseña del usuario en la base de datos y genera un nuevo token
     * @param array $data Datos para actualizar la contraseña (pass)
     * @return mixed Usuario actualizado o null en caso de error
     */
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
            return $this->load($token);
        } catch (PDOException $e) {
            return $this->returnError($e->getMessage());
        }
    }

    /**
     * verifyUser
     * Marca al usuario como verificado en la base de datos
     * @return mixed Usuario actualizado o null en caso de error
     */
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
    /**
     * setSupermercadosOcultos
     * Actualiza la lista de supermercados ocultos para el usuario
     * @param array $data Datos con los supermercados a ocultar
     * @return mixed Usuario actualizado o null en caso de error
     */
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
    /**
     * sendValidationEmail
     * Envía un correo electrónico de validación al usuario
     * @param string $email Correo electrónico del usuario
     * @param string $token Token de verificación
     * @return void
     */
    public function sendValidationEmail($email, $token){
        $subject = "Valida tu cuenta en ".APP_NAME;
        $verification_link = APP_URL . "verifyMail?mail=" . urlencode($email) . "&verify_key=" . $token;
        $message = "Para validar tu cuenta en ".APP_NAME." haz click en el siguiente enlace:\n\n".$verification_link;
        $headers = "From: ".APP_EMAIL;
        mail($email, $subject, $message, $headers);
    }
    public function validateUser($email, $token){
        $this->load($token);
        if ($this->user["email"]==$email)
        {
            $this->verifyUser();
        }
    }
    public function getLastResult() {
        return $this->is_done;
    }

    public function getDevice() {
        return $this->device;
    }

    public function getIdUsuario() {
        return isset($this->user["id"]) ? $this->user["id"] : null;
    }

    public function getId() {
        return isset($this->user["id"]) ? $this->user["id"] : null;
    }

    public function getUser() {
        return $this->user;
    }

    public function isLoaded(){
        return $this->user != null;
    }

    public function getErrorMsg() {
        return $this->error_msg;
    }

    public function getSupermercadosOcultos(){
        return isset($this->user["supermercados_ocultos"]) ? $this->user["supermercados_ocultos"] : "-1";
    }

    public function getLog(){
        return $this->log;
    }
}
