# Documentación de la API Hungry

## Descripción General

Hungry es una API RESTful desarrollada en PHP que permite gestionar usuarios, productos, supermercados y categorías. La API está diseñada para ser utilizada por aplicaciones móviles y web que necesiten gestionar listas de compras y comparar precios de productos en diferentes supermercados.

## Estructura del Proyecto

```bash
/
├── docs/                  # Documentación del proyecto
├── includes/              # Archivos de inclusión
│   ├── actions/           # Acciones de la API (endpoints)
│   │   ├── get/           # Endpoints GET
│   │   └── post/          # Endpoints POST
│   ├── clases/            # Clases PHP
│   │   ├── categorias.class.inc.php    # Clase para gestionar categorías
│   │   ├── productos.class.inc.php     # Clase para gestionar productos
│   │   ├── supermercados.class.inc.php # Clase para gestionar supermercados
│   │   └── usuario.class.inc.php       # Clase para gestionar usuarios
│   ├── config/            # Archivos de configuración
│   │   ├── config.db.inc.php           # Configuración de base de datos
│   │   ├── config.inc.php              # Configuración general
│   │   ├── config.mail.inc.php         # Configuración de correo
│   │   └── config.utf8.inc.php         # Configuración de codificación
│   ├── php/               # Funciones y utilidades PHP
│   │   ├── json/          # Funciones relacionadas con JSON
│   │   ├── jwt/           # Implementación de JWT
│   │   └── server/        # Funciones del servidor
│   └── loader.inc.php     # Cargador de archivos
├── .env                   # Variables de entorno
└── index.php              # Punto de entrada de la API
```

## Configuración

La API utiliza un archivo `.env` para la configuración. A continuación se muestra un ejemplo de configuración:

```ini
[DB]
DB_HOST="your_database_host"
DB_USER="your_database_user"
DB_PASS="your_database_password"
DB_NAME="your_database_name"

[JWT]
SECRET_KEY="your_jwt_secret_key"

[MAIL]
USER="your_smtp_email"
PASS="your_smtp_password"
HOST="smtp.gmail.com"
PORT="587"
SECURE="tls"

[APP]
NAME="Hungry by @trystan4861"
API_URL="https://your-api-url.com/api/"
EMAIL_USER="your_app_email"
EMAIL_PASS="your_app_email_password"
```

## Autenticación

La API utiliza JSON Web Tokens (JWT) para la autenticación. Para acceder a los endpoints protegidos, se debe incluir un token válido en la cabecera de la petición:

```json
Authorization: Bearer <token>
```

## Endpoints

A continuación se detallan los endpoints disponibles en la API. Para una documentación más detallada, consulta el archivo [API_ENDPOINTS.md](API_ENDPOINTS.md).

### Usuarios

#### Endpoint: Registro de Usuario

- **URL**: `/register`
- **Método**: `POST`
- **Descripción**: Registra un nuevo usuario en el sistema.
- **Parámetros**:
  - `email`: Correo electrónico del usuario (obligatorio)
  - `pass`: Contraseña del usuario (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "token": "jwt_token"
  }
  ```

#### Endpoint: Verificación de Correo

- **URL**: `/verifyMail`
- **Método**: `GET`
- **Descripción**: Verifica el correo electrónico de un usuario.
- **Parámetros**:
  - `mail`: Correo electrónico a verificar (obligatorio)
  - `verify_key`: Clave de verificación (opcional)
- **Comportamiento**:
  - Si no se proporciona `verify_key`: genera una clave y envía un correo de verificación
  - Si se proporciona `verify_key`: verifica el correo usando la clave proporcionada

#### Endpoint: Inicio de Sesión

- **URL**: `/login`
- **Método**: `POST`
- **Descripción**: Inicia sesión con un usuario existente.
- **Parámetros**:
  - `email`: Correo electrónico del usuario (obligatorio)
  - `pass`: Contraseña del usuario (obligatorio)
  - `fingerid`: ID del dispositivo (opcional)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "token": "jwt_token",
    "device": {
      "id": 1,
      "fingerID": "device123",
      "is_master": 1
    }
  }
  ```

#### Endpoint: Obtener ID de Usuario

- **URL**: `/getIdUsuario`
- **Método**: `GET`
- **Descripción**: Obtiene el ID del usuario autenticado.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "id_usuario": 1
  }
  ```

### Datos

#### Endpoint: Obtener Todos los Datos

- **URL**: `/getAll`
- **Método**: `GET`
- **Descripción**: Obtiene todos los datos del usuario (categorías, productos y supermercados).
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "categorias": [...],
    "productos": [...],
    "supermercados": [...]
  }
  ```

#### Endpoint: Obtener Datos

- **URL**: `/getData`
- **Método**: `POST`
- **Descripción**: Obtiene datos del servidor sin sincronización automática.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `fingerid`: ID del dispositivo (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "data": {
      "loginData": {...},
      "categorias": [...],
      "supermercados": [...],
      "productos": [...]
    }
  }
  ```

#### Endpoint: Sincronizar Datos

- **URL**: `/syncData`
- **Método**: `POST`
- **Descripción**: Sincroniza datos entre el cliente y el servidor.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `fingerid`: ID del dispositivo (obligatorio)
  - `data`: Objeto con datos a sincronizar (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "data": {
      "categorias": [...],
      "supermercados": [...],
      "productos": [...]
    }
  }
  ```

### Categorías

#### Endpoint: Obtener Categorías

- **URL**: `/getCategorias`
- **Método**: `GET`
- **Descripción**: Obtiene las categorías del usuario.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "categorias": [
      {
        "id": 1,
        "id_categoria": 1,
        "text": "Categoría 1",
        "bgColor": "#cccccc",
        "visible": 1,
        "timestamp": 1617234567
      }
    ]
  }
  ```

#### Endpoint: Actualizar Texto de Categoría

- **URL**: `/updateCategoriaText`
- **Método**: `POST`
- **Descripción**: Actualiza el texto de una categoría.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_categoria`: ID de la categoría (obligatorio)
  - `text`: Nuevo texto para la categoría (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

#### Endpoint: Actualizar Visibilidad de Categoría

- **URL**: `/updateCategoriaVisible`
- **Método**: `POST`
- **Descripción**: Actualiza la visibilidad de una categoría.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_categoria`: ID de la categoría (obligatorio)
  - `visible`: Visibilidad (0=oculto, 1=visible) (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

### Productos

#### Endpoint: Nuevo Producto

- **URL**: `/newProducto`
- **Método**: `POST`
- **Descripción**: Crea un nuevo producto.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_categoria`: ID de la categoría (obligatorio)
  - `id_supermercado`: ID del supermercado (obligatorio)
  - `text`: Nombre del producto (obligatorio)
  - `amount`: Cantidad (opcional, por defecto: 1)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "id_producto": 1
  }
  ```

#### Endpoint: Actualizar Producto

- **URL**: `/updateProducto`
- **Método**: `POST`
- **Descripción**: Actualiza un producto existente.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_producto`: ID del producto (obligatorio)
  - `id_categoria`: ID de la categoría (opcional)
  - `id_supermercado`: ID del supermercado (opcional)
  - `text`: Nombre del producto (opcional)
  - `amount`: Cantidad (opcional)
  - `selected`: Estado de selección (0/1) (opcional)
  - `done`: Estado de completado (0/1) (opcional)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

#### Endpoint: Actualizar Cantidad

- **URL**: `/updateProductoAmount`
- **Método**: `POST`
- **Descripción**: Actualiza la cantidad de un producto.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_producto`: ID del producto (obligatorio)
  - `amount`: Nueva cantidad (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

#### Endpoint: Actualizar Estado de Selección

- **URL**: `/updateProductoSelected`
- **Método**: `POST`
- **Descripción**: Actualiza el estado de selección de un producto.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_producto`: ID del producto (obligatorio)
  - `selected`: Estado de selección (0/1) (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

#### Endpoint: Actualizar Estado de Completado

- **URL**: `/updateProductoDone`
- **Método**: `POST`
- **Descripción**: Actualiza el estado de completado de un producto.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_producto`: ID del producto (obligatorio)
  - `done`: Estado de completado (0/1) (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

### Supermercados

#### Endpoint: Obtener Supermercados

- **URL**: `/getSupermercados`
- **Método**: `GET`
- **Descripción**: Obtiene la lista de supermercados.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true,
    "supermercados": [
      {
        "id": 1,
        "text": "Supermercado 1",
        "logo": "default.svg",
        "visible": 1,
        "order": 1,
        "timestamp": 1617234567
      }
    ]
  }
  ```

#### Endpoint: Actualizar Visibilidad de Supermercado

- **URL**: `/updateSupermercadoVisible`
- **Método**: `POST`
- **Descripción**: Actualiza la visibilidad de un supermercado.
- **Parámetros**:
  - `token`: Token de autenticación (obligatorio)
  - `id_supermercado`: ID del supermercado (obligatorio)
  - `visible`: Visibilidad (0=oculto, 1=visible) (obligatorio)
- **Respuesta exitosa**:

  ```json
  {
    "result": true
  }
  ```

## Manejo de Errores

La API devuelve errores en formato JSON con la siguiente estructura:

```json
{
  "result": false,
  "error_msg": "Descripción del error"
}
```

Los códigos de estado HTTP también se utilizan para indicar el resultado de la petición:

- `200 OK`: La petición se ha completado correctamente.
- `400 Bad Request`: La petición contiene parámetros inválidos o faltantes.
- `401 Unauthorized`: No se ha proporcionado un token válido.
- `404 Not Found`: El recurso solicitado no existe.
- `500 Internal Server Error`: Error interno del servidor.

## Clases Principales

### Clase Usuario

La clase `Usuario` gestiona todas las operaciones relacionadas con los usuarios, como registro, inicio de sesión, verificación de correo electrónico, etc.

Métodos principales:

- `load($data)`: Carga un usuario desde la base de datos.
- `createUser($data)`: Crea un nuevo usuario.
- `verifyUser()`: Marca al usuario como verificado.
- `sendValidationEmail($email, $token)`: Envía un correo de verificación.
- `validateUser($email, $token)`: Valida un usuario con su correo y token.
- `getId()`: Obtiene el ID del usuario cargado.
- `getUser()`: Obtiene los datos del usuario cargado.
- `isLoaded()`: Verifica si el usuario está cargado.

### Clase Productos

La clase `Productos` gestiona todas las operaciones relacionadas con los productos.

Métodos principales:

- `getProductos()`: Obtiene todos los productos del usuario.
- `newProducto($data)`: Crea un nuevo producto.
- `updateProducto($data)`: Actualiza un producto existente.
- `updateProductoAmount($id_producto, $amount)`: Actualiza la cantidad de un producto.
- `updateProductoSelected($id_producto, $selected)`: Actualiza el estado de selección de un producto.
- `updateProductoDone($id_producto, $done)`: Actualiza el estado de completado de un producto.
- `getLastResult()`: Obtiene el resultado de la última operación.
- `getErrorMsg()`: Obtiene el mensaje de error de la última operación.

### Clase Supermercados

La clase `Supermercados` gestiona todas las operaciones relacionadas con los supermercados.

Métodos principales:

- `getSupermercados()`: Obtiene todos los supermercados.
- `updateSupermercadoVisible($id_supermercado, $visible)`: Actualiza la visibilidad de un supermercado.
- `getLastResult()`: Obtiene el resultado de la última operación.
- `getErrorMsg()`: Obtiene el mensaje de error de la última operación.

### Clase Categorias

La clase `Categorias` gestiona todas las operaciones relacionadas con las categorías de productos.

Métodos principales:

- `getCategorias()`: Obtiene todas las categorías.
- `updateCategoriaText($id_categoria, $text)`: Actualiza el texto de una categoría.
- `updateCategoriaVisible($id_categoria, $visible)`: Actualiza la visibilidad de una categoría.
- `getLastResult()`: Obtiene el resultado de la última operación.
- `getErrorMsg()`: Obtiene el mensaje de error de la última operación.

## Funciones Auxiliares

### JWT - Autenticación

La implementación de JWT se encuentra en `includes/php/jwt/jwt.inc.php` y proporciona las siguientes funciones:

- `JWT::encode($payload, $key)`: Codifica un payload en un token JWT.
- `JWT::decode($jwt, $key)`: Decodifica un token JWT y devuelve el payload.

### Correos Electrónicos

Las funciones para el envío de correos se configuran en `includes/config/config.mail.inc.php` y utilizan la biblioteca PHPMailer para enviar correos electrónicos.

### Sincronización

Las funciones para la sincronización de datos se encuentran en `includes/actions/post/syncData.inc.php`:

- `sincronizarCategorias($categorias, $clientCategorias)`: Sincroniza las categorías entre el cliente y el servidor.
- `sincronizarSupermercados($supermercados, $clientSupermercados)`: Sincroniza los supermercados entre el cliente y el servidor.
- `sincronizarProductos($productos, $clientProductos)`: Sincroniza los productos entre el cliente y el servidor.
- `writeLog($message, $type)`: Escribe un mensaje en el log.
- `formatException($e)`: Formatea una excepción para el log.

## Requisitos del Sistema

- PHP 7.4 o superior
- Extensión PDO para MySQL
- Extensión OpenSSL
- Extensión mbstring
- Composer (para gestionar dependencias)

## Dependencias

- PHPMailer: Para el envío de correos electrónicos.

## Instalación

1. Clonar el repositorio.
2. Ejecutar `composer install` para instalar las dependencias.
3. Copiar `.env.example` a `.env` y configurar las variables de entorno.
4. Configurar el servidor web para que apunte a la carpeta raíz del proyecto.

## Seguridad

La API implementa las siguientes medidas de seguridad:

- Autenticación mediante JWT.
- Contraseñas almacenadas de forma segura.
- Verificación de correo electrónico.
- Validación de datos de entrada.
- Protección contra inyección SQL mediante consultas preparadas.
