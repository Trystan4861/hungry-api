-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Servidor: db5015576258.hosting-data.io
-- Tiempo de generación: 31-03-2025 a las 20:17:08
-- Versión del servidor: 10.6.15-MariaDB-log
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `dbs12723242`
--
CREATE DATABASE IF NOT EXISTS `dbs12723242` DEFAULT CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci;
USE `dbs12723242`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `bgColor` varchar(7) NOT NULL DEFAULT '#cccccc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `fk_id_usuario` int(11) NOT NULL,
  `fk_id_categoria` int(11) NOT NULL,
  `fk_id_supermercado` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT 1,
  `selected` tinyint(1) NOT NULL DEFAULT 0,
  `done` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `supermercados`
--

CREATE TABLE `supermercados` (
  `id` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT 'default.svg',
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pass` varchar(255) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `microtime` double DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `lastChangeTimestamp` timestamp NULL DEFAULT current_timestamp(),
  `lastLoginTimestamp` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Disparadores `usuarios`
--
DELIMITER $$
CREATE TRIGGER `crear_datos_usuario` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 0,'Categoría  1',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 1,'Categoría  2',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 2,'Categoría  3',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 3,'Categoría  4',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 4,'Categoría  5',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 5,'Categoría  6',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 6,'Categoría  7',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 7,'Categoría  8',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 8,'Categoría  9',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id, 9,'Categoría 10',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,10,'Categoría 11',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,11,'Categoría 12',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,12,'Categoría 13',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,13,'Categoría 14',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,14,'Categoría 15',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,15,'Categoría 16',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,16,'Categoría 17',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,17,'Categoría 18',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,18,'Categoría 19',1);
    INSERT INTO usuarios_categorias (fk_id_usuario, fk_id_categoria, text, visible) VALUES (NEW.id,19,'Categoría 20',1);

    INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible, `order`) VALUES (New.id, 0, 1, 1);
    INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible, `order`) VALUES (New.id, 1, 1, 2);
    INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible, `order`) VALUES (New.id, 2, 1, 3);
    INSERT INTO usuarios_supermercados (fk_id_usuario, fk_id_supermercado, visible, `order`) VALUES (New.id, 3, 1, 4);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_categorias`
--

CREATE TABLE `usuarios_categorias` (
  `id` int(11) NOT NULL,
  `fk_id_usuario` int(11) NOT NULL,
  `fk_id_categoria` int(11) NOT NULL,
  `text` varchar(255) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_devices`
--

CREATE TABLE `usuarios_devices` (
  `id` int(11) NOT NULL,
  `fk_id_usuario` int(11) NOT NULL,
  `fingerID` varchar(255) NOT NULL,
  `is_master` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_supermercados`
--

CREATE TABLE `usuarios_supermercados` (
  `id` int(11) NOT NULL,
  `fk_id_usuario` int(11) NOT NULL,
  `fk_id_supermercado` int(11) NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `order` int(11) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_productos_usuarios` (`fk_id_usuario`),
  ADD KEY `fk_productos_categorias` (`fk_id_categoria`),
  ADD KEY `fk_productos_supermercados` (`fk_id_supermercado`);

--
-- Indices de la tabla `supermercados`
--
ALTER TABLE `supermercados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios_categorias`
--
ALTER TABLE `usuarios_categorias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuarios_categorias_usuarios` (`fk_id_usuario`),
  ADD KEY `fk_usuarios_categorias_categorias` (`fk_id_categoria`);

--
-- Indices de la tabla `usuarios_devices`
--
ALTER TABLE `usuarios_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuarios_devices_usuarios` (`fk_id_usuario`);

--
-- Indices de la tabla `usuarios_supermercados`
--
ALTER TABLE `usuarios_supermercados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuarios_supermercados_usuarios` (`fk_id_usuario`),
  ADD KEY `fk_usuarios_supermercados_supermercados` (`fk_id_supermercado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `supermercados`
--
ALTER TABLE `supermercados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_categorias`
--
ALTER TABLE `usuarios_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_devices`
--
ALTER TABLE `usuarios_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_supermercados`
--
ALTER TABLE `usuarios_supermercados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`fk_id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_productos_supermercados` FOREIGN KEY (`fk_id_supermercado`) REFERENCES `supermercados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_productos_usuarios` FOREIGN KEY (`fk_id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_categorias`
--
ALTER TABLE `usuarios_categorias`
  ADD CONSTRAINT `fk_usuarios_categorias_categorias` FOREIGN KEY (`fk_id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuarios_categorias_usuarios` FOREIGN KEY (`fk_id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_devices`
--
ALTER TABLE `usuarios_devices`
  ADD CONSTRAINT `fk_usuarios_devices_usuarios` FOREIGN KEY (`fk_id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios_supermercados`
--
ALTER TABLE `usuarios_supermercados`
  ADD CONSTRAINT `fk_usuarios_supermercados_supermercados` FOREIGN KEY (`fk_id_supermercado`) REFERENCES `supermercados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usuarios_supermercados_usuarios` FOREIGN KEY (`fk_id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;
