# Guía de Instalación

Esta guía detalla los pasos necesarios para instalar y configurar la API Hungry en un entorno de desarrollo o producción.

## Requisitos del Sistema

Antes de comenzar la instalación, asegúrate de que tu sistema cumple con los siguientes requisitos:

- PHP 7.4 o superior
- Extensiones de PHP:
  - PDO (con soporte para MySQL)
  - OpenSSL
  - mbstring
  - json
  - curl
- MySQL 5.7 o superior
- Composer (para gestionar dependencias)
- Servidor web (Apache, Nginx, etc.)

## Pasos de Instalación

### 1. Clonar el Repositorio

```bash
git clone https://github.com/tu-usuario/hungry-api.git
cd hungry-api
```

### 2. Instalar Dependencias

Utiliza Composer para instalar las dependencias del proyecto:

```bash
composer install
```

### 3. Configurar el Entorno

Copia el archivo `.env.example` a `.env` y configura las variables de entorno:

```bash
cp .env.example .env
```

Edita el archivo `.env` con la configuración de tu entorno:

```ini
[DB]
DB_HOST="tu_host_mysql"
DB_USER="tu_usuario_mysql"
DB_PASS="tu_contraseña_mysql"
DB_NAME="tu_base_de_datos"

[JWT]
SECRET_KEY="tu_clave_secreta_jwt"

[MAIL]
USER="tu_correo_smtp"
PASS="tu_contraseña_smtp"
HOST="tu_servidor_smtp"
PORT="587"
SECURE="tls"

[APP]
NAME="Hungry by @tu_usuario"
API_URL="https://tu-dominio.com/api/"
EMAIL_USER="tu_correo_aplicacion"
EMAIL_PASS="tu_contraseña_aplicacion"
```

### 4. Crear la Base de Datos

Crea una base de datos MySQL para la aplicación:

```sql
CREATE DATABASE hungry_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Importar la Estructura de la Base de Datos

Importa el archivo SQL con la estructura de la base de datos:

```bash
mysql -u tu_usuario -p hungry_db < database/structure.sql
```

Si no tienes el archivo `structure.sql`, puedes crear las tablas manualmente:

```sql
-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    pass VARCHAR(255) NOT NULL,
    token TEXT,
    microtime DOUBLE,
    verified TINYINT(1) DEFAULT 0,
    supermercados_ocultos VARCHAR(255) DEFAULT '-1'
);

-- Tabla de dispositivos de usuarios
CREATE TABLE usuarios_devices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_id_usuario INT NOT NULL,
    fingerID VARCHAR(255) NOT NULL,
    is_master TINYINT(1) DEFAULT 0,
    FOREIGN KEY (fk_id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de supermercados
CREATE TABLE supermercados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    logo VARCHAR(255)
);

-- Tabla de productos
CREATE TABLE productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    id_supermercado INT NOT NULL,
    FOREIGN KEY (id_supermercado) REFERENCES supermercados(id) ON DELETE CASCADE
);

-- Tabla de listas
CREATE TABLE listas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fk_id_usuario INT NOT NULL,
    FOREIGN KEY (fk_id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de relación entre productos y listas
CREATE TABLE productos_listas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fk_id_lista INT NOT NULL,
    fk_id_producto INT NOT NULL,
    FOREIGN KEY (fk_id_lista) REFERENCES listas(id) ON DELETE CASCADE,
    FOREIGN KEY (fk_id_producto) REFERENCES productos(id) ON DELETE CASCADE,
    UNIQUE KEY (fk_id_lista, fk_id_producto)
);
```

### 6. Configurar el Servidor Web

#### Apache

Crea un archivo `.htaccess` en la raíz del proyecto:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

Configura un VirtualHost en Apache:

```apache
<VirtualHost *:80>
    ServerName api.ejemplo.com
    DocumentRoot /ruta/a/hungry-api

    <Directory /ruta/a/hungry-api>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hungry-api-error.log
    CustomLog ${APACHE_LOG_DIR}/hungry-api-access.log combined
</VirtualHost>
```

#### Nginx

Configura un servidor en Nginx:

```nginx
server {
    listen 80;
    server_name api.ejemplo.com;
    root /ruta/a/hungry-api;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 7. Configurar Permisos

Asegúrate de que los directorios necesarios tengan los permisos adecuados:

```bash
chmod -R 755 .
chmod -R 777 logs
```

### 8. Verificar la Instalación

Para verificar que la instalación se ha realizado correctamente, accede a la URL de la API:

```url
https://api.ejemplo.com/
```

Deberías recibir una respuesta JSON indicando que la API está funcionando.

### 9. Probar el Envío de Correos

Utiliza los scripts de prueba incluidos para verificar que el envío de correos funciona correctamente:

```url
https://api.ejemplo.com/test_mail_advanced.php
https://api.ejemplo.com/test_smtp_connection.php
```

## Configuración Adicional

### Configuración de CORS

Si necesitas permitir solicitudes desde dominios específicos, puedes configurar CORS añadiendo las siguientes cabeceras en el archivo `index.php`:

```php
// Configuración de CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
```

### Configuración de SSL/TLS

Para una mayor seguridad, es recomendable configurar SSL/TLS en tu servidor web. Puedes obtener certificados gratuitos de Let's Encrypt.

#### Apache Config

```apache
<VirtualHost *:443>
    ServerName api.ejemplo.com
    DocumentRoot /ruta/a/hungry-api

    SSLEngine on
    SSLCertificateFile /ruta/a/certificado.crt
    SSLCertificateKeyFile /ruta/a/clave-privada.key

    <Directory /ruta/a/hungry-api>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/hungry-api-error.log
    CustomLog ${APACHE_LOG_DIR}/hungry-api-access.log combined
</VirtualHost>
```

#### Nginx Config

```nginx
server {
    listen 443 ssl;
    server_name api.ejemplo.com;
    root /ruta/a/hungry-api;

    ssl_certificate /ruta/a/certificado.crt;
    ssl_certificate_key /ruta/a/clave-privada.key;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

## Solución de Problemas

### Problemas Comunes

1. **Error de conexión a la base de datos**:
   - Verifica las credenciales en el archivo `.env`.
   - Asegúrate de que el servidor MySQL esté en ejecución.
   - Comprueba que el usuario tenga permisos para acceder a la base de datos.

2. **Error de envío de correos**:
   - Verifica las credenciales SMTP en el archivo `.env`.
   - Comprueba que el servidor SMTP esté configurado correctamente.
   - Utiliza los scripts de prueba para diagnosticar problemas.

3. **Errores 500 Internal Server Error**:
   - Revisa los logs de error del servidor web.
   - Verifica que PHP tenga las extensiones requeridas habilitadas.
   - Comprueba los permisos de los archivos y directorios.

4. **Problemas de CORS**:
   - Configura correctamente las cabeceras CORS en el archivo `index.php`.
   - Verifica que el dominio de origen esté permitido.

## Entornos de Producción

Para entornos de producción, se recomienda:

1. **Optimizar PHP**:
   - Habilitar OPcache para mejorar el rendimiento.
   - Configurar adecuadamente los límites de memoria y tiempo de ejecución.

2. **Seguridad**:
   - Utilizar HTTPS con certificados válidos.
   - Configurar firewalls y reglas de seguridad.
   - Mantener actualizado PHP y todas las dependencias.

3. **Monitorización**:
   - Implementar herramientas de monitorización para detectar problemas.
   - Configurar alertas para errores críticos.

4. **Copias de Seguridad**:
   - Realizar copias de seguridad regulares de la base de datos.
   - Implementar un sistema de recuperación ante desastres.

## Actualización

Para actualizar la API a una nueva versión:

1. Realizar una copia de seguridad de la base de datos y los archivos.
2. Descargar la nueva versión del repositorio.
3. Actualizar las dependencias con Composer.
4. Aplicar las migraciones de la base de datos si las hay.
5. Actualizar el archivo `.env` con nuevas variables si es necesario.
6. Reiniciar el servidor web si es necesario.
