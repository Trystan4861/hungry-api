# Sistema de Autenticación

Este documento detalla el sistema de autenticación implementado en la API Hungry, incluyendo el flujo de autenticación, la generación y validación de tokens, y las medidas de seguridad implementadas.

## Tecnología Utilizada

La API Hungry utiliza JSON Web Tokens (JWT) para la autenticación de usuarios. JWT es un estándar abierto (RFC 7519) que define una forma compacta y autónoma de transmitir información de forma segura entre partes como un objeto JSON.

## Implementación de JWT

La implementación de JWT se encuentra en el archivo `includes/php/jwt/jwt.inc.php` y proporciona las siguientes funciones:

### JWT::encode($payload, $key)

Codifica un payload en un token JWT.

```php
public static function encode($payload, $key)
{
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}
```

### JWT::decode($jwt, $key)

Decodifica un token JWT y devuelve el payload.

```php
public static function decode($jwt, $key)
{
    $jwtParts = explode('.', $jwt);
    if (count($jwtParts) !== 3) {
        throw new Exception('Invalid JWT format');
    }
    $signature = str_replace(['-', '_'], ['+', '/'], $jwtParts[2]);
    $decodedSignature = base64_decode($signature);
    $expectedSignature = hash_hmac('sha256', $jwtParts[0] . "." . $jwtParts[1], $key, true);
    if (!hash_equals($decodedSignature, $expectedSignature)) {
        throw new Exception('Invalid signature');
    }
    $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $jwtParts[1])), true);
    return $payload;
}
```

## Generación de Tokens

Los tokens se generan utilizando la función `generate_token` definida en `includes/php/json/json.inc.php`:

```php
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
```

Esta función toma un array con los datos del usuario (contraseña y timestamp) y genera un token JWT utilizando la clave secreta definida en el archivo `.env`.

## Flujo de Autenticación

### Registro de Usuario

1. El usuario envía sus datos de registro (email y contraseña) al endpoint `/register`.
2. Se valida el formato del email y la fortaleza de la contraseña.
3. Se genera un timestamp (`microtime`) y un token JWT.
4. Se almacena el usuario en la base de datos con su email, contraseña, token y timestamp.
5. Si la verificación de correo está habilitada (`MUST_VALIDATE`), se envía un correo de verificación.
6. Si la verificación no está habilitada, se marca al usuario como verificado y se devuelve el token.

### Verificación de Correo Electrónico

1. El usuario recibe un correo con un enlace que contiene su email y token de verificación.
2. Al hacer clic en el enlace, se envía una petición al endpoint `/verifyMail` con el email y el token.
3. Se verifica que el token coincida con el almacenado en la base de datos.
4. Si el token es válido, se marca al usuario como verificado.

### Inicio de Sesión

1. El usuario envía sus credenciales (email y contraseña) al endpoint `/login`.
2. Se verifica que las credenciales sean correctas.
3. Si las credenciales son válidas, se devuelve el token JWT almacenado para ese usuario.
4. Si se proporciona un `fingerid` (identificador del dispositivo), se registra en la base de datos.

### Autenticación en Endpoints Protegidos

1. El cliente incluye el token JWT en la cabecera de la petición o como parámetro.
2. El servidor verifica la validez del token.
3. Si el token es válido, se procesa la petición.
4. Si el token no es válido o ha expirado, se devuelve un error de autenticación.

## Validación de Tokens

La validación de tokens se realiza en cada endpoint protegido. El proceso es el siguiente:

1. Se extrae el token de la petición (cabecera o parámetro).
2. Se carga el usuario asociado al token utilizando el método `load` de la clase `Usuario`.
3. Si el usuario existe y el token coincide, se procesa la petición.
4. Si el usuario no existe o el token no coincide, se devuelve un error de autenticación.

```php
// Ejemplo de validación de token en un endpoint protegido
if (!isset($data["token"]) || empty($data["token"])) {
    $json["error_msg"] = "Es necesario proporcionar un token de autenticación";
    return;
}

$usuario = new Usuario();
$id_usuario = $usuario->load($data["token"]);

if (!$id_usuario) {
    $json["error_msg"] = "Token inválido o expirado";
    return;
}

// Procesar la petición...
```

## Seguridad

### Almacenamiento de Contraseñas

Las contraseñas se almacenan en la base de datos utilizando un algoritmo de hash seguro. Aunque no se utiliza directamente `password_hash()` y `password_verify()`, se implementa una lógica similar para proteger las contraseñas.

### Protección contra Ataques

1. **Cross-Site Request Forgery (CSRF)**: Se utiliza JWT para proteger contra ataques CSRF, ya que cada petición debe incluir un token válido.

2. **Cross-Site Scripting (XSS)**: Se implementa la validación y escape de datos de entrada y salida para prevenir ataques XSS.

3. **Inyección SQL**: Se utilizan consultas preparadas (PDO) para prevenir ataques de inyección SQL.

4. **Fuerza Bruta**: Se implementan medidas para limitar los intentos de inicio de sesión fallidos.

### _Verificación de Correo Electrónico_

Se implementa un sistema de verificación de correo electrónico para asegurar que los usuarios tienen acceso a los correos que registran. Esto ayuda a prevenir el registro de cuentas falsas y el spam.

## Mejoras Futuras

1. **Expiración de Tokens**: Implementar un sistema de expiración de tokens para aumentar la seguridad.

2. **Refresh Tokens**: Implementar tokens de actualización para permitir la renovación de tokens sin necesidad de volver a iniciar sesión.

3. **Autenticación de Dos Factores (2FA)**: Añadir soporte para autenticación de dos factores.

4. **OAuth/OpenID Connect**: Integrar con proveedores de identidad externos (Google, Facebook, etc.).

5. **Rate Limiting**: Implementar límites de tasa para prevenir ataques de fuerza bruta.

## Ejemplo de Uso

**Nota:** Las contraseñas en los ejemplos no están codificadas para facilitar su uso. En una aplicación real, las contraseñas deben ser codificadas antes de enviarse al servidor usando MD5.

### Ejemplo de Registro de Usuario

```bash
curl -X POST https://api.ejemplo.com/register \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","pass":"Contraseña123"}'
```

### Ejemplo de Inicio de Sesión

```bash
curl -X POST https://api.ejemplo.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","pass":"Contraseña123"}'
```

### Ejemplo de Acceso a Endpoint Protegido

```bash
curl -X GET "https://api.ejemplo.com/getProductos?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

o

```bash
curl -X GET https://api.ejemplo.com/getProductos \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```
