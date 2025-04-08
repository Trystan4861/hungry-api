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
                    $sql = "SELECT * FROM usuarios WHERE token = :token";
                    $consulta = $this->DAO->prepare($sql);
                    $consulta->bindValue(":token", $data["token"], PDO::PARAM_STR);
                    error_log("Buscando usuario con token: " . $data["token"]);
                }
            } else {
                // Si es un string, asumimos que es un token
                $sql = "SELECT * FROM usuarios WHERE token = :token";
                $consulta = $this->DAO->prepare($sql);
                $consulta->bindValue(":token", $data, PDO::PARAM_STR);
                error_log("Buscando usuario con token (string): " . $data);
            }
            // Ejecutamos la consulta y capturamos posibles errores
            try {
                $consulta->execute();
                $usuario = $consulta->fetch(PDO::FETCH_ASSOC);

                if ($usuario) {
                    error_log("Usuario encontrado con ID: " . $usuario["id"] . " y email: " . $usuario["email"]);
                    $this->device = 0;
                    if (isset($data["fingerid"])) {
                        try {
                            // Ahora procedemos con la consulta normal
                            $sql = "SELECT COALESCE(MAX(id), NULL) AS id,
                            COALESCE(MAX(fk_id_usuario), :id) AS fk_id_usuario,
                            COALESCE(MAX(fingerID), NULL) AS fingerID,
                            (SELECT COUNT(*) FROM usuarios_devices WHERE fk_id_usuario = :id) AS total_registros
                            FROM usuarios_devices
                            WHERE fk_id_usuario = :id AND fingerID = :fingerID;";
                            $consulta = $this->DAO->prepare($sql);
                            $consulta->bindValue(":id", $usuario["id"]);
                            $consulta->bindValue(":fingerID", $data["fingerid"]);
                            $consulta->execute();
                            $result = $consulta->fetch(PDO::FETCH_ASSOC);
                            if ($result["id"] == null) {
                                $sql = "INSERT INTO usuarios_devices (fk_id_usuario, fingerID, is_master) VALUES (:id, :fingerID, :is_master)";
                                $consulta = $this->DAO->prepare($sql);
                                $consulta->bindValue(":id", $usuario["id"]);
                                $consulta->bindValue(":fingerID", $data["fingerid"]);
                                $consulta->bindValue(":is_master", $result["total_registros"] == 0 ? 1 : 0, PDO::PARAM_BOOL);
                                $consulta->execute();
                                $result["id"] = $this->DAO->lastInsertId();
                            }
                            $this->device = $result["id"];
                        } catch (PDOException $e) {
                            $this->log["error"] = $e->getMessage();
                            error_log("Error en Usuario::load con fingerID: " . $e->getMessage());
                            // No fallamos completamente, solo registramos el error y continuamos
                            $this->device = 0;
                        }
                    }

                    $this->user = $usuario;
                    $this->is_done = true;
                    return $this->user['id'];
                } else {
                    error_log("No se encontró ningún usuario con los criterios de búsqueda");
                    $this->is_done = false;
                    return null;
                }
            } catch (PDOException $e) {
                error_log("Error en la consulta SQL en Usuario::load: " . $e->getMessage());
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
     * Actualiza la lista de supermercados ocultos para el usuario en la tabla usuarios_supermercados
     * @param array $data Datos con los supermercados a ocultar
     * @return mixed Usuario actualizado o null en caso de error
     */
    public function setSupermercadosOcultos($data){
        try {
            // Comenzamos una transacción para asegurar la integridad de los datos
            $this->DAO->beginTransaction();

            // Convertimos los datos a un array si no lo son
            $supermercados = $data["supermercados_ocultos"];
            if (!is_array($supermercados)) {
                if ($supermercados === null || $supermercados === "") {
                    $supermercados = [];
                } else {
                    $supermercados = explode(",", $supermercados);
                }
            }

            // Primero, establecemos todos los supermercados como visibles para este usuario
            $sql = "UPDATE usuarios_supermercados SET visible = 1 WHERE fk_id_usuario = :id_usuario";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":id_usuario", $this->user["id"]);
            $consulta->execute();

            // Luego, para cada supermercado en la lista, lo marcamos como oculto (visible = 0)
            if (!empty($supermercados) && $supermercados[0] != "-1") {
                foreach ($supermercados as $id_supermercado) {
                    // Verificamos si ya existe una relación para este usuario y supermercado
                    $sql = "SELECT id FROM usuarios_supermercados
                            WHERE fk_id_usuario = :id_usuario AND fk_id_supermercado = :id_supermercado";
                    $consulta = $this->DAO->prepare($sql);
                    $consulta->bindValue(":id_usuario", $this->user["id"]);
                    $consulta->bindValue(":id_supermercado", $id_supermercado);
                    $consulta->execute();
                    $existe = $consulta->fetch(PDO::FETCH_ASSOC);

                    if ($existe) {
                        // Si existe, actualizamos su visibilidad
                        $sql = "UPDATE usuarios_supermercados SET visible = 0
                                WHERE fk_id_usuario = :id_usuario AND fk_id_supermercado = :id_supermercado";
                        $consulta = $this->DAO->prepare($sql);
                        $consulta->bindValue(":id_usuario", $this->user["id"]);
                        $consulta->bindValue(":id_supermercado", $id_supermercado);
                        $consulta->execute();
                    } else {
                        // Si no existe, creamos un nuevo registro
                        $sql = "INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible)
                                VALUES (:id_usuario, :id_supermercado, 0)";
                        $consulta = $this->DAO->prepare($sql);
                        $consulta->bindValue(":id_usuario", $this->user["id"]);
                        $consulta->bindValue(":id_supermercado", $id_supermercado);
                        $consulta->execute();
                    }
                }
            }

            // Confirmamos la transacción
            $this->DAO->commit();

            // Devolvemos el usuario actualizado
            return $this->load($this->user["token"]);
        } catch (PDOException $e) {
            // Si hay un error, revertimos la transacción
            $this->DAO->rollBack();
            error_log("Error al actualizar supermercados ocultos: " . $e->getMessage());
            return $this->returnError($e->getMessage());
        }
    }
    /**
     * sendValidationEmail
     * Envía un correo electrónico de validación al usuario
     * @param string $email Correo electrónico del usuario
     * @param string $token Token de verificación
     * @return bool True si el correo se envió correctamente, False en caso contrario
     */
    public function sendValidationEmail($email, $token){
        $subject = "Valida tu cuenta en ".APP_NAME;
        $verification_link = APP_URL . "verifyMail?mail=" . urlencode($email) . "&verify_key=" . $token;

        // Crear mensaje en formato HTML
        $htmlMessage = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Verificación de cuenta</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #f9f9f9;
                    border-radius: 5px;
                    padding: 20px;
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                }
                .content {
                    background-color: #fff;
                    border-radius: 5px;
                    padding: 20px;
                    margin-bottom: 20px;
                }
                .button {
                    display: inline-block;
                    background-color: lightgreen;
                    color: white !important;
                    font-weight: bold;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 5px;
                    margin-top: 20px;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    color: #777;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>".APP_NAME."</h1>
                </div>
                <div class='content'>
                    <h2>Verificación de cuenta</h2>
                    <p>Gracias por registrarte en ".APP_NAME.".</p>
                    <p>Para validar tu cuenta, haz clic en el siguiente botón:</p>
                    <p style='text-align: center;'>
                        <a href='".$verification_link."' class='button'>Verificar mi cuenta</a>
                    </p>
                    <p>O copia y pega el siguiente enlace en tu navegador:</p>
                    <p>".$verification_link."</p>
                </div>
                <div class='footer'>
                    <p>Este correo fue enviado automáticamente. Por favor, no respondas a este mensaje.</p>
                </div>
            </div>
        </body>
        </html>";

        // Mensaje de texto plano como alternativa
        $textMessage = "Para validar tu cuenta en ".APP_NAME." haz click en el siguiente enlace:\n\n".$verification_link;

        try {
            // Intentamos enviar el correo
            $result = sendMail($email, isset($this->user["nombre"]) ? $this->user["nombre"] : "Usuario", $subject, $htmlMessage, $textMessage);

            // Registramos el resultado
            if ($result) {
                error_log("Correo de verificación enviado correctamente a: $email");
                return true;
            } else {
                error_log("Error al enviar correo de verificación a: $email");
                $this->log["error"] = "Error al enviar correo de verificación";
                return false;
            }
        }
        catch(Exception $e){
            error_log("Excepción al enviar correo de verificación a $email: ".$e->getMessage());
            $this->log["error"] = "Error enviando email de validación: ".$e->getMessage();
            return false;
        }
    }
    /**
     * validateUser
     * Valida un usuario utilizando su correo electrónico y token
     * @param string $email Correo electrónico del usuario
     * @param string $token Token de verificación
     * @return bool True si la validación fue exitosa, False en caso contrario
     */
    public function validateUser($email, $token){
        // Registramos información para depuración
        error_log("Intentando validar usuario con email: $email y token: $token");

        // Intentamos cargar el usuario con el token proporcionado
        $this->load($token);

        // Verificamos si se cargó el usuario y si el correo coincide
        if ($this->isLoaded() && $this->user["email"] == $email) {
            error_log("Usuario cargado correctamente. Procediendo a verificar.");
            $this->verifyUser();
            return true;
        } else {
            error_log("Error al validar usuario. Token inválido o correo no coincidente.");
            if ($this->isLoaded()) {
                error_log("Usuario cargado pero el correo no coincide. Email en BD: " . $this->user["email"]);
            } else {
                error_log("No se pudo cargar el usuario con el token proporcionado.");
            }
            return false;
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

    /**
     * getToken
     * Obtiene el token del usuario actual
     * @return string|null Token del usuario o null si no está cargado
     */
    public function getToken() {
        return isset($this->user["token"]) ? $this->user["token"] : null;
    }

    /**
     * isLoaded
     * Verifica si el usuario está cargado
     * @return bool True si el usuario está cargado, False en caso contrario
     */
    public function isLoaded() {
        return isset($this->user) && !empty($this->user) && isset($this->user["id"]);
    }

    public function getUser() {
        return $this->user;
    }

    public function getErrorMsg() {
        return $this->error_msg;
    }

    /**
     * getSupermercadosOcultos
     * Obtiene la lista de supermercados ocultos para el usuario desde la tabla usuarios_supermercados
     * @return string Lista de IDs de supermercados ocultos separados por comas, o "-1" si no hay ninguno
     */
    public function getSupermercadosOcultos(){
        if (!isset($this->user["id"])) {
            return "-1";
        }

        try {
            $sql = "SELECT fk_id_supermercado FROM usuarios_supermercados
                    WHERE fk_id_usuario = :id_usuario AND visible = 0";
            $consulta = $this->DAO->prepare($sql);
            $consulta->bindValue(":id_usuario", $this->user["id"]);
            $consulta->execute();
            $result = $consulta->fetchAll(PDO::FETCH_COLUMN);

            if (empty($result)) {
                return "-1";
            }

            return implode(",", $result);
        } catch (PDOException $e) {
            error_log("Error al obtener supermercados ocultos: " . $e->getMessage());
            return "-1";
        }
    }

    public function getLog(){
        return $this->log;
    }
}
