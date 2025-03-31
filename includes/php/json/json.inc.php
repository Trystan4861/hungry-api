<?php
  use JWT;
  /**
   * generate_token
   * Genera un token JWT a partir de los datos proporcionados
   * @param array $data Datos para generar el token (debe contener pass y microtime)
   * @return string Token JWT generado
   */
  function generate_token($data){
    global $env;
    // Verificamos que existan los datos necesarios
    if (!isset($data["pass"]) || !isset($data["microtime"])) {
      error_log("Error: Faltan datos para generar el token (pass y/o microtime)");
      return md5(uniqid(rand(), true)); // Generamos un token aleatorio como fallback
    }

    // Creamos el payload para el token
    $payload = $data["pass"] . $data["microtime"];

    // Usamos la clave secreta del archivo .env
    $secret_key = isset($env["JWT"]["SECRET_KEY"]) ? $env["JWT"]["SECRET_KEY"] : "mpc-hungry-by-trystan4861";

    // Generamos y devolvemos el token
    return JWT::encode($payload, $secret_key);
  }