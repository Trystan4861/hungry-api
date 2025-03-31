# Estructura de la Base de Datos

Este documento detalla la estructura de la base de datos utilizada por la API Hungry, incluyendo tablas, campos, relaciones y restricciones.

## Diagrama Entidad-Relación

```text
+-------------+       +---------------+       +---------------+
|   usuarios  |       |   categorias  |       | supermercados |
+-------------+       +---------------+       +---------------+
| id          |       | id            |       | id            |
| email       |       | text          |       | text          |
| pass        |       | bgColor       |       | logo          |
| token       |       +---------------+       | timestamp     |
| microtime   |              ^                +---------------+
| verified    |              |                       ^
| lastChange  |              |                       |
| lastLogin   |              |                       |
+-------------+              |                       |
      ^                      |                       |
      |                      |                       |
      |           +-------------------+              |
      +---------->| usuarios_categorias|             |
      |           +-------------------+              |
      |           | id                |              |
      |           | fk_id_usuario     |              |
      |           | fk_id_categoria   |              |
      |           | text              |              |
      |           | visible           |              |
      |           | timestamp         |              |
      |           +-------------------+              |
      |                                              |
      |           +-------------------+              |
      +---------->| usuarios_devices  |              |
      |           +-------------------+              |
      |           | id                |              |
      |           | fk_id_usuario     |              |
      |           | fingerID          |              |
      |           | is_master         |              |
      |           | timestamp         |              |
      |           +-------------------+              |
      |                                              |
      |           +------------------------+         |
      +---------->| usuarios_supermercados |-------->+
      |           +------------------------+         |
      |           | id                     |         |
      |           | fk_id_usuario          |         |
      |           | fk_id_supermercado     |         |
      |           | visible                |         |
      |           | order                  |         |
      |           | timestamp              |         |
      |           +------------------------+         |
      |                                              |
      |           +------------------------+         |
      +---------->|      productos        |-------->+
                  +------------------------+
                  | id                     |
                  | id_producto            |
                  | fk_id_usuario          |
                  | fk_id_categoria        |
                  | fk_id_supermercado     |
                  | text                   |
                  | amount                 |
                  | selected               |
                  | done                   |
                  | timestamp              |
                  +------------------------+
```

## Tablas

### usuarios

Almacena la información de los usuarios registrados en el sistema.

| Campo                | Tipo         | Descripción                                   |
|----------------------|--------------|-----------------------------------------------|
| id                   | int(11)      | Identificador único del usuario (PK)          |
| email                | varchar(255) | Correo electrónico del usuario (único)        |
| pass                 | varchar(255) | Contraseña del usuario                        |
| token                | varchar(255) | Token para autenticación                      |
| microtime            | double       | Timestamp usado para generar el token         |
| verified             | tinyint(1)   | Indica si el correo ha sido verificado (0/1)  |
| lastChangeTimestamp  | timestamp    | Fecha y hora del último cambio                |
| lastLoginTimestamp   | timestamp    | Fecha y hora del último inicio de sesión      |

#### usuarios - Índices

- `PRIMARY KEY` en `id`
- `UNIQUE KEY` en `email`

#### usuarios - Disparadores

- `crear_datos_usuario`: Después de insertar un nuevo usuario, crea automáticamente registros en `usuarios_categorias` y `usuarios_supermercados` con valores predeterminados.

### usuarios_devices

Almacena información sobre los dispositivos asociados a los usuarios.

| Campo         | Tipo         | Descripción                                   |
|---------------|--------------|-----------------------------------------------|
| id            | int(11)      | Identificador único del dispositivo (PK)      |
| fk_id_usuario | int(11)      | ID del usuario al que pertenece (FK)          |
| fingerID      | varchar(255) | Identificador único del dispositivo           |
| is_master     | tinyint(1)   | Indica si es el dispositivo principal (0/1)   |
| timestamp     | timestamp    | Fecha y hora de registro del dispositivo      |

#### usuarios_devices - Índices

- `PRIMARY KEY` en `id`
- `KEY` en `fk_id_usuario` (índice para la clave foránea)
- `FOREIGN KEY` `fk_usuarios_devices_usuarios` en `fk_id_usuario` referenciando `usuarios(id)` con `ON DELETE CASCADE`

### categorias

Almacena información sobre las categorías disponibles en el sistema.

| Campo    | Tipo         | Descripción                                   |
|----------|--------------|-----------------------------------------------|
| id       | int(11)      | Identificador único de la categoría (PK)      |
| text     | varchar(255) | Nombre de la categoría                        |
| bgColor  | varchar(7)   | Color de fondo en formato hexadecimal         |

#### categorias - Índices

- `PRIMARY KEY` en `id`

### usuarios_categorias

Almacena la relación entre usuarios y categorías, permitiendo personalización.

| Campo            | Tipo         | Descripción                                   |
|------------------|--------------|-----------------------------------------------|
| id               | int(11)      | Identificador único de la relación (PK)       |
| fk_id_usuario    | int(11)      | ID del usuario (FK)                           |
| fk_id_categoria  | int(11)      | ID de la categoría (FK)                       |
| text             | varchar(255) | Nombre personalizado de la categoría          |
| visible          | tinyint(1)   | Indica si la categoría es visible (1) o no (0)|
| timestamp        | timestamp    | Fecha y hora de la última actualización       |

#### usuarios_categorias - Índices

- `PRIMARY KEY` en `id`
- `KEY` en `fk_id_usuario` (índice para la clave foránea)
- `KEY` en `fk_id_categoria` (índice para la clave foránea)
- `FOREIGN KEY` `fk_usuarios_categorias_usuarios` en `fk_id_usuario` referenciando `usuarios(id)` con `ON DELETE CASCADE`
- `FOREIGN KEY` `fk_usuarios_categorias_categorias` en `fk_id_categoria` referenciando `categorias(id)` con `ON DELETE CASCADE`

### supermercados

Almacena información sobre los supermercados disponibles en el sistema.

| Campo     | Tipo         | Descripción                                   |
|-----------|--------------|-----------------------------------------------|
| id        | int(11)      | Identificador único del supermercado (PK)     |
| text      | varchar(255) | Nombre del supermercado                       |
| logo      | varchar(255) | URL del logo del supermercado                 |
| timestamp | timestamp    | Fecha y hora de la última actualización       |

#### supermercados - Índices

- `PRIMARY KEY` en `id`

### usuarios_supermercados

Almacena la relación entre usuarios y supermercados, incluyendo la configuración de visibilidad.

| Campo             | Tipo         | Descripción                                   |
|-------------------|--------------|-----------------------------------------------|
| id                | int(11)      | Identificador único de la relación (PK)       |
| fk_id_usuario     | int(11)      | ID del usuario (FK)                           |
| fk_id_supermercado| int(11)      | ID del supermercado (FK)                      |
| visible           | tinyint(1)   | Indica si el supermercado es visible (1) o no (0) |
| order             | int(11)      | Orden de visualización del supermercado       |
| timestamp         | timestamp    | Fecha y hora de la última actualización       |

#### usuarios_supermercados - Índices

- `PRIMARY KEY` en `id`
- `KEY` en `fk_id_usuario` (índice para la clave foránea)
- `KEY` en `fk_id_supermercado` (índice para la clave foránea)
- `FOREIGN KEY` `fk_usuarios_supermercados_usuarios` en `fk_id_usuario` referenciando `usuarios(id)` con `ON DELETE CASCADE`
- `FOREIGN KEY` `fk_usuarios_supermercados_supermercados` en `fk_id_supermercado` referenciando `supermercados(id)` con `ON DELETE CASCADE`

### productos

Almacena información sobre los productos de los usuarios.

| Campo              | Tipo         | Descripción                                   |
|--------------------|--------------|-----------------------------------------------|
| id                 | int(11)      | Identificador único del producto (PK)         |
| id_producto        | int(11)      | Identificador secundario del producto         |
| fk_id_usuario      | int(11)      | ID del usuario propietario (FK)               |
| fk_id_categoria    | int(11)      | ID de la categoría del producto (FK)          |
| fk_id_supermercado | int(11)      | ID del supermercado asociado (FK)             |
| text               | varchar(255) | Nombre o descripción del producto             |
| amount             | int(11)      | Cantidad del producto                         |
| selected           | tinyint(1)   | Indica si el producto está seleccionado       |
| done               | tinyint(1)   | Indica si el producto está completado         |
| timestamp          | timestamp    | Fecha y hora de la última actualización       |

#### productos - Índices

- `PRIMARY KEY` en `id`
- `KEY` en `fk_id_usuario` (índice para la clave foránea)
- `KEY` en `fk_id_categoria` (índice para la clave foránea)
- `KEY` en `fk_id_supermercado` (índice para la clave foránea)
- `FOREIGN KEY` `fk_productos_usuarios` en `fk_id_usuario` referenciando `usuarios(id)` con `ON DELETE CASCADE`
- `FOREIGN KEY` `fk_productos_categorias` en `fk_id_categoria` referenciando `categorias(id)` con `ON DELETE CASCADE`
- `FOREIGN KEY` `fk_productos_supermercados` en `fk_id_supermercado` referenciando `supermercados(id)` con `ON DELETE CASCADE`

## Relaciones

1. **usuarios - usuarios_devices**: Un usuario puede tener muchos dispositivos (1:N)
   - `usuarios.id` → `usuarios_devices.fk_id_usuario`

2. **usuarios - usuarios_categorias**: Un usuario puede tener muchas categorías personalizadas (1:N)
   - `usuarios.id` → `usuarios_categorias.fk_id_usuario`

3. **categorias - usuarios_categorias**: Una categoría puede estar asociada a muchos usuarios (1:N)
   - `categorias.id` → `usuarios_categorias.fk_id_categoria`

4. **usuarios - usuarios_supermercados**: Un usuario puede tener relaciones con muchos supermercados (1:N)
   - `usuarios.id` → `usuarios_supermercados.fk_id_usuario`

5. **supermercados - usuarios_supermercados**: Un supermercado puede estar relacionado con muchos usuarios (1:N)
   - `supermercados.id` → `usuarios_supermercados.fk_id_supermercado`

6. **usuarios - productos**: Un usuario puede tener muchos productos (1:N)
   - `usuarios.id` → `productos.fk_id_usuario`

7. **categorias - productos**: Una categoría puede contener muchos productos (1:N)
   - `categorias.id` → `productos.fk_id_categoria`

8. **supermercados - productos**: Un supermercado puede tener muchos productos (1:N)
   - `supermercados.id` → `productos.fk_id_supermercado`

## Restricciones

1. **Integridad referencial**: Todas las claves foráneas tienen restricciones de integridad referencial.
   - `ON DELETE CASCADE`: Al eliminar un registro, se eliminan todos los registros relacionados. Por ejemplo, al eliminar un usuario, se eliminarán automáticamente todos sus productos, dispositivos y relaciones con supermercados y categorías.

2. **Unicidad**:
   - El correo electrónico (`usuarios.email`) debe ser único.

3. **Valores predeterminados**:
   - `categorias.bgColor`: '#cccccc'
   - `productos.amount`: 1
   - `productos.selected`: 0
   - `productos.done`: 0
   - `usuarios_categorias.visible`: 1
   - `usuarios_devices.is_master`: 0
   - `usuarios_supermercados.visible`: 1
   - `usuarios_supermercados.order`: 0
   - `supermercados.logo`: 'default.svg'

## Ejemplos de Consultas SQL

### Obtener todos los productos de un usuario

```sql
SELECT p.*, c.text as categoria_nombre, s.text as supermercado_nombre
FROM productos p
JOIN categorias c ON p.fk_id_categoria = c.id
JOIN supermercados s ON p.fk_id_supermercado = s.id
WHERE p.fk_id_usuario = :id_usuario;
```

### Obtener productos por categoría para un usuario

```sql
SELECT p.*
FROM productos p
WHERE p.fk_id_usuario = :id_usuario AND p.fk_id_categoria = :id_categoria;
```

### Obtener productos por supermercado para un usuario

```sql
SELECT p.*
FROM productos p
WHERE p.fk_id_usuario = :id_usuario AND p.fk_id_supermercado = :id_supermercado;
```

### Verificar si un usuario existe

```sql
SELECT * FROM usuarios WHERE email = :email;
```

### Actualizar el token de un usuario

```sql
UPDATE usuarios SET token = :token, microtime = :microtime, lastLoginTimestamp = CURRENT_TIMESTAMP WHERE id = :id;
```

### Marcar un usuario como verificado

```sql
UPDATE usuarios SET verified = 1 WHERE id = :id;
```

### Obtener supermercados visibles para un usuario

```sql
SELECT s.*, us.visible, us.order
FROM supermercados s
LEFT JOIN usuarios_supermercados us ON s.id = us.fk_id_supermercado AND us.fk_id_usuario = :id_usuario
WHERE us.visible = 1 OR us.visible IS NULL
ORDER BY COALESCE(us.order, 999999), s.text;
```

### Actualizar la visibilidad de un supermercado para un usuario

```sql
INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible, `order`)
VALUES (:id_usuario, :id_supermercado, :visible, :order)
ON DUPLICATE KEY UPDATE visible = :visible, `order` = :order;
```

### Obtener categorías visibles para un usuario

```sql
SELECT c.*, uc.text, uc.visible
FROM categorias c
JOIN usuarios_categorias uc ON c.id = uc.fk_id_categoria
WHERE uc.fk_id_usuario = :id_usuario AND uc.visible = 1;
```

### Añadir un nuevo producto

```sql
INSERT INTO productos (id_producto, fk_id_usuario, fk_id_categoria, fk_id_supermercado, text, amount)
VALUES (:id_producto, :id_usuario, :id_categoria, :id_supermercado, :text, :amount);
```

### Marcar un producto como completado

```sql
UPDATE productos SET done = 1 WHERE id = :id AND fk_id_usuario = :id_usuario;
```

## Notas sobre la Implementación

1. **Disparadores**: Se utiliza un disparador `crear_datos_usuario` para inicializar automáticamente las categorías y supermercados predeterminados para cada nuevo usuario.

2. **Índices**: Se han creado índices en las columnas frecuentemente utilizadas en cláusulas WHERE y JOIN para mejorar el rendimiento de las consultas.

3. **Tipos de datos**: Se han elegido tipos de datos apropiados para cada campo, considerando el tamaño y la naturaleza de los datos.

4. **Normalización**: La base de datos está normalizada para minimizar la redundancia y mejorar la integridad de los datos.

5. **Seguridad**: Las contraseñas se almacenan de forma segura (no en texto plano) y los tokens tienen un tiempo de expiración controlado por el campo `microtime`.

6. **Personalización**: El sistema permite a los usuarios personalizar tanto las categorías como los supermercados, incluyendo visibilidad y orden de visualización.

7. **Timestamps**: Todas las tablas principales incluyen campos de timestamp para seguimiento de cambios y auditoría.
