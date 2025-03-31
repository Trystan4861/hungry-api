<?php
  $json["categorias"]=$categorias->getCategorias();
  $json["productos"]=$productos->getProductos();
  // Usamos el ID de usuario para obtener los supermercados según su configuración de visibilidad
  $id_usuario = $user->getId();
  $supermercados_obj = new Supermercados(null, $id_usuario);
  $json["supermercados"] = $supermercados_obj->getSupermercados();
