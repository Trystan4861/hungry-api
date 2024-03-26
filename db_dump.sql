SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE categorias (
  id int(11) NOT NULL,
  fk_id_usuario int(11) NOT NULL,
  id_categoria tinyint(4) NOT NULL,
  text varchar(40) NOT NULL,
  bgColor varchar(7) NOT NULL,
  visible tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE productos (
  id int(11) NOT NULL,
  fk_id_usuario int(11) NOT NULL,
  fk_id_categoria int(11) NOT NULL,
  fk_id_supermercado int(11) NOT NULL,
  text varchar(40) NOT NULL,
  amount tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

CREATE TABLE usuarios (
  id int(11) NOT NULL,
  email varchar(60) NOT NULL,
  pass varchar(32) NOT NULL,
  token varchar(128) NOT NULL,
  verified tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
DELIMITER $$
CREATE TRIGGER `crear_categorias_usuario` AFTER INSERT ON `usuarios` FOR EACH ROW BEGIN
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 0,'Categoría  1','#d83c3d',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 1,'Categoría  2','#d8993c',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 2,'Categoría  3','#b9d83c',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 3,'Categoría  4','#5bd83c',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 4,'Categoría  5','#3dd87a',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 5,'Categoría  6','#47ffff',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 6,'Categoría  7','#3b7ad7',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 7,'Categoría  8','#5b3cd8',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 8,'Categoría  9','#b83cd8',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id, 9,'Categoría 10','#d83ba4',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,10,'Categoría 11','#6f1918',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,11,'Categoría 12','#704c1a',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,12,'Categoría 13','#5d6f19',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,13,'Categoría 14','#2b6f18',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,14,'Categoría 15','#1f8448',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,15,'Categoría 16','#196f70',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,16,'Categoría 17','#183c6e',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,17,'Categoría 18','#2c186f',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,18,'Categoría 19','#5e186e',1);
    INSERT INTO categorias (fk_id_usuario, id_categoria, text, bgColor, visible) VALUES (NEW.id,19,'Categoría 20','#6e1952',1);
END
$$
DELIMITER ;


ALTER TABLE categorias
  ADD PRIMARY KEY (id);

ALTER TABLE productos
  ADD PRIMARY KEY (id);

ALTER TABLE usuarios
  ADD PRIMARY KEY (id),
  ADD UNIQUE KEY email (email);


ALTER TABLE categorias
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE productos
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE usuarios
  MODIFY id int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
