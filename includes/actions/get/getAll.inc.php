<?php
  $categorias= new Categorias();
  $productos= new Productos();
  $json["categorias"]=$categorias->getByUserId($id_usuario);
  $json["productos"]=$productos->getByUserId($id_usuario);