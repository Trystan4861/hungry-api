# Documentación de Endpoints de la API Hungry

Este documento detalla todos los endpoints disponibles en la API Hungry, incluyendo sus parámetros, respuestas y ejemplos de uso.

## Índice

1. [Usuarios](#usuarios)
   - [Registro](#registro-de-usuario)
   - [Verificación de Correo](#verificación-de-correo-electrónico)
   - [Inicio de Sesión](#inicio-de-sesión)
   - [Obtener ID de Usuario](#obtener-id-de-usuario)

2. [Datos](#datos)
   - [Obtener Todos los Datos](#obtener-todos-los-datos)
   - [Obtener Datos](#obtener-datos)
   - [Sincronizar Datos](#sincronizar-datos)

3. [Categorías](#categorías)
   - [Obtener Categorías](#obtener-categorías)
   - [Actualizar Texto de Categoría](#actualizar-texto-de-categoría)
   - [Actualizar Visibilidad de Categoría](#actualizar-visibilidad-de-categoría)

4. [Productos](#productos)
   - [Nuevo Producto](#nuevo-producto)
   - [Actualizar Producto](#actualizar-producto)
   - [Actualizar Cantidad de Producto](#actualizar-cantidad-de-producto)
   - [Actualizar Estado de Selección de Producto](#actualizar-estado-de-selección-de-producto)
   - [Actualizar Estado de Completado de Producto](#actualizar-estado-de-completado-de-producto)

5. [Supermercados](#supermercados)
   - [Obtener Supermercados](#obtener-supermercados)
   - [Actualizar Visibilidad de Supermercado](#actualizar-visibilidad-de-supermercado)

## Usuarios

### Registro de Usuario

Registra un nuevo usuario en el sistema.

- **URL**: `/register`
- **Método**: `POST`
- **Autenticación**: No requerida

#### register - Parámetros

| Nombre | Tipo   | Requerido | Descripción                |
|--------|--------|-----------|----------------------------|
| email  | string | Sí        | Correo electrónico del usuario |
| pass   | string | Sí        | Contraseña del usuario     |

#### register - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### register - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "El correo electrónico ya está registrado en el sistema"
}
```

#### register - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/register \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","pass":"Contraseña123"}'
```

### Verificación de Correo Electrónico

Verifica el correo electrónico de un usuario.

- **URL**: `/verifyMail`
- **Método**: `GET`
- **Autenticación**: No requerida

#### verifyMail - Parámetros

| Nombre     | Tipo   | Requerido | Descripción                |
|------------|--------|-----------|----------------------------|
| mail       | string | Sí        | Correo electrónico a verificar |
| verify_key | string | No        | Clave de verificación      |

#### verifyMail - Comportamiento

- Si no se proporciona `verify_key`: genera una clave y envía un correo de verificación
- Si se proporciona `verify_key`: verifica el correo usando la clave proporcionada

#### verifyMail - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "message": "Correo electrónico verificado correctamente"
}
```

#### verifyMail - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "El enlace de verificación no es válido o ha expirado"
}
```

#### verifyMail - Ejemplo de Uso

```bash
# Solicitar correo de verificación
curl -X GET "https://api.hungry.com/verifyMail?mail=usuario@ejemplo.com"

# Verificar correo con clave
curl -X GET "https://api.hungry.com/verifyMail?mail=usuario@ejemplo.com&verify_key=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Inicio de Sesión

Inicia sesión con un usuario existente.

- **URL**: `/login`
- **Método**: `POST`
- **Autenticación**: No requerida

#### login - Parámetros

| Nombre   | Tipo   | Requerido | Descripción                |
|----------|--------|-----------|----------------------------|
| email    | string | Sí        | Correo electrónico del usuario |
| pass     | string | Sí        | Contraseña del usuario     |
| fingerid | string | No        | ID del dispositivo         |

#### login - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "device": {
    "id": 1,
    "fingerID": "device123",
    "is_master": 1
  }
}
```

#### login - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Credenciales incorrectas"
}
```

#### login - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/login \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@ejemplo.com","pass":"Contraseña123","fingerid":"device123"}'
```

### Obtener ID de Usuario

Obtiene el ID del usuario autenticado.

- **URL**: `/getIdUsuario`
- **Método**: `GET`
- **Autenticación**: Requerida (token JWT)

#### getIdUsuario - Parámetros

| Nombre | Tipo   | Requerido | Descripción                |
|--------|--------|-----------|----------------------------|
| token  | string | Sí        | Token de autenticación     |

#### getIdUsuario - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "id_usuario": 1
}
```

#### getIdUsuario - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Token inválido o expirado"
}
```

#### getIdUsuario - Ejemplo de Uso

```bash
curl -X GET "https://api.hungry.com/getIdUsuario?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

## Datos

### Obtener Todos los Datos

Obtiene todos los datos del usuario (categorías, productos y supermercados).

- **URL**: `/getAll`
- **Método**: `GET`
- **Autenticación**: Requerida (token JWT)

#### getAll - Parámetros

| Nombre | Tipo   | Requerido | Descripción                |
|--------|--------|-----------|----------------------------|
| token  | string | Sí        | Token de autenticación     |

#### getAll - Respuesta Exitosa (200 OK)

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
  ],
  "productos": [
    {
      "id": 1,
      "id_producto": 1,
      "text": "Producto 1",
      "fk_id_categoria": 1,
      "fk_id_supermercado": 1,
      "amount": 1,
      "selected": 0,
      "done": 0,
      "timestamp": 1617234567
    }
  ],
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

#### getAll - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Token inválido o expirado"
}
```

#### getAll - Ejemplo de Uso

```bash
curl -X GET "https://api.hungry.com/getAll?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Obtener Datos

Obtiene datos del servidor sin sincronización automática.

- **URL**: `/getData`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### getData - Parámetros

| Nombre   | Tipo   | Requerido | Descripción                |
|----------|--------|-----------|----------------------------|
| token    | string | Sí        | Token de autenticación     |
| fingerid | string | Sí        | ID del dispositivo         |

#### getData - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "data": {
    "loginData": {
      "email": "usuario@ejemplo.com",
      "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
      "fingerID": "device123",
      "logged": true
    },
    "categorias": [...],
    "supermercados": [...],
    "productos": [...]
  }
}
```

#### getData - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Faltan datos obligatorios para obtener los datos"
}
```

#### getData - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/getData \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","fingerid":"device123"}'
```

### Sincronizar Datos

Sincroniza datos entre el cliente y el servidor.

- **URL**: `/syncData`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### syncData - Parámetros

| Nombre   | Tipo   | Requerido | Descripción                |
|----------|--------|-----------|----------------------------|
| token    | string | Sí        | Token de autenticación     |
| fingerid | string | Sí        | ID del dispositivo         |
| data     | object | Sí        | Datos a sincronizar        |

#### syncData - Estructura del objeto `data`

```json
{
  "categorias": [...],
  "supermercados": [...],
  "productos": [...]
}
```

#### syncData - Respuesta Exitosa (200 OK)

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

#### syncData - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Faltan datos obligatorios para la sincronización"
}
```

#### syncData - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/syncData \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","fingerid":"device123","data":{"categorias":[...],"supermercados":[...],"productos":[...]}}'
```

## Categorías

### Obtener Categorías

Obtiene las categorías del usuario.

- **URL**: `/getCategorias`
- **Método**: `GET`
- **Autenticación**: Requerida (token JWT)

#### getCategorias - Parámetros

| Nombre | Tipo   | Requerido | Descripción                |
|--------|--------|-----------|----------------------------|
| token  | string | Sí        | Token de autenticación     |

#### getCategorias - Respuesta Exitosa (200 OK)

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

#### getCategorias - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Token inválido o expirado"
}
```

#### getCategorias - Ejemplo de Uso

```bash
curl -X GET "https://api.hungry.com/getCategorias?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Actualizar Texto de Categoría

Actualiza el texto de una categoría.

- **URL**: `/updateCategoriaText`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateCategoriaText - Parámetros

| Nombre       | Tipo   | Requerido | Descripción                |
|--------------|--------|-----------|----------------------------|
| token        | string | Sí        | Token de autenticación     |
| id_categoria | int    | Sí        | ID de la categoría         |
| text         | string | Sí        | Nuevo texto para la categoría |

#### updateCategoriaText - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateCategoriaText - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar la categoría"
}
```

#### updateCategoriaText - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateCategoriaText \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_categoria":1,"text":"Nueva Categoría"}'
```

### Actualizar Visibilidad de Categoría

Actualiza la visibilidad de una categoría.

- **URL**: `/updateCategoriaVisible`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateCategoriaVisible - Parámetros

| Nombre       | Tipo   | Requerido | Descripción                |
|--------------|--------|-----------|----------------------------|
| token        | string | Sí        | Token de autenticación     |
| id_categoria | int    | Sí        | ID de la categoría         |
| visible      | int    | Sí        | Visibilidad (0=oculto, 1=visible) |

#### updateCategoriaVisible - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateCategoriaVisible - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar la visibilidad de la categoría"
}
```

#### updateCategoriaVisible - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateCategoriaVisible \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_categoria":1,"visible":0}'
```

## Productos

### Nuevo Producto

Crea un nuevo producto.

- **URL**: `/newProducto`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### newProducto - Parámetros

| Nombre         | Tipo   | Requerido | Descripción                |
|----------------|--------|-----------|----------------------------|
| token          | string | Sí        | Token de autenticación     |
| id_categoria   | int    | Sí        | ID de la categoría         |
| id_supermercado| int    | Sí        | ID del supermercado        |
| text           | string | Sí        | Nombre del producto        |
| amount         | int    | No        | Cantidad (por defecto: 1)  |

#### newProducto - Respuesta Exitosa (200 OK)

```json
{
  "result": true,
  "id_producto": 1
}
```

#### newProducto - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo crear el producto"
}
```

#### newProducto - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/newProducto \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_categoria":1,"id_supermercado":1,"text":"Leche","amount":2}'
```

### Actualizar Producto

Actualiza un producto existente.

- **URL**: `/updateProducto`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateProducto - Parámetros

| Nombre         | Tipo   | Requerido | Descripción                |
|----------------|--------|-----------|----------------------------|
| token          | string | Sí        | Token de autenticación     |
| id_producto    | int    | Sí        | ID del producto            |
| id_categoria   | int    | No        | ID de la categoría         |
| id_supermercado| int    | No        | ID del supermercado        |
| text           | string | No        | Nombre del producto        |
| amount         | int    | No        | Cantidad                   |
| selected       | int    | No        | Estado de selección (0/1)  |
| done           | int    | No        | Estado de completado (0/1) |

#### updateProducto - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateProducto - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar el producto"
}
```

#### updateProducto - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateProducto \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_producto":1,"text":"Leche Desnatada","id_categoria":2}'
```

### Actualizar Nombre de Producto

Actualiza El nombre de un producto.

- **URL**: `/updateProductoText`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateProductoText - Parámetros

| Nombre      | Tipo   | Requerido | Descripción                |
|-------------|--------|-----------|----------------------------|
| token       | string | Sí        | Token de autenticación     |
| id_producto | int    | Sí        | ID del producto            |
| Text        | string | Sí        | Nuevo nombre               |

#### updateProductoText - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateProductoText - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar la cantidad del producto"
}
```

#### updateProductoText - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateProductoText \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_producto":1,"Text":"Nuevo Nombre"}'
```

### Actualizar Cantidad de Producto

Actualiza la cantidad de un producto.

- **URL**: `/updateProductoAmount`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateProductoAmount - Parámetros

| Nombre      | Tipo   | Requerido | Descripción                |
|-------------|--------|-----------|----------------------------|
| token       | string | Sí        | Token de autenticación     |
| id_producto | int    | Sí        | ID del producto            |
| amount      | int    | Sí        | Nueva cantidad             |

#### updateProductoAmount - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateProductoAmount - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar la cantidad del producto"
}
```

#### updateProductoAmount - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateProductoAmount \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_producto":1,"amount":3}'
```

### Actualizar Estado de Selección de Producto

Actualiza el estado de selección de un producto.

- **URL**: `/updateProductoSelected`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateProductoSelected - Parámetros

| Nombre      | Tipo   | Requerido | Descripción                |
|-------------|--------|-----------|----------------------------|
| token       | string | Sí        | Token de autenticación     |
| id_producto | int    | Sí        | ID del producto            |
| selected    | int    | Sí        | Estado de selección (0/1)  |

#### updateProductoSelected - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateProductoSelected - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar el estado de selección del producto"
}
```

#### updateProductoSelected - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateProductoSelected \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_producto":1,"selected":1}'
```

### Actualizar Estado de Completado de Producto

Actualiza el estado de completado de un producto.

- **URL**: `/updateProductoDone`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateProductoDone - Parámetros

| Nombre      | Tipo   | Requerido | Descripción                |
|-------------|--------|-----------|----------------------------|
| token       | string | Sí        | Token de autenticación     |
| id_producto | int    | Sí        | ID del producto            |
| done        | int    | Sí        | Estado de completado (0/1) |

#### updateProductoDone - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateProductoDone - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar el estado de completado del producto"
}
```

#### updateProductoDone - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateProductoDone \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_producto":1,"done":1}'
```

## Supermercados

### Obtener Supermercados

Obtiene la lista de supermercados.

- **URL**: `/getSupermercados`
- **Método**: `GET`
- **Autenticación**: Requerida (token JWT)

#### getSupermercados - Parámetros

| Nombre | Tipo   | Requerido | Descripción                |
|--------|--------|-----------|----------------------------|
| token  | string | Sí        | Token de autenticación     |

#### getSupermercados - Respuesta Exitosa (200 OK)

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

#### getSupermercados - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "Token inválido o expirado"
}
```

#### getSupermercados - Ejemplo de Uso

```bash
curl -X GET "https://api.hungry.com/getSupermercados?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Actualizar Visibilidad de Supermercado

Actualiza la visibilidad de un supermercado.

- **URL**: `/updateSupermercadoVisible`
- **Método**: `POST`
- **Autenticación**: Requerida (token JWT)

#### updateSupermercadoVisible - Parámetros

| Nombre         | Tipo   | Requerido | Descripción                |
|----------------|--------|-----------|----------------------------|
| token          | string | Sí        | Token de autenticación     |
| id_supermercado| int    | Sí        | ID del supermercado        |
| visible        | int    | Sí        | Visibilidad (0=oculto, 1=visible) |

#### updateSupermercadoVisible - Respuesta Exitosa (200 OK)

```json
{
  "result": true
}
```

#### updateSupermercadoVisible - Respuesta de Error

```json
{
  "result": false,
  "error_msg": "No se pudo actualizar la visibilidad del supermercado"
}
```

#### updateSupermercadoVisible - Ejemplo de Uso

```bash
curl -X POST https://api.hungry.com/updateSupermercadoVisible \
  -H "Content-Type: application/json" \
  -d '{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...","id_supermercado":1,"visible":0}'
```je": "Producto añadido a la lista correctamente"
}
```
