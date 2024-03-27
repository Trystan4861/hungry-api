<?php
function removePrefix($prefix, $from) {
    $result = array();
    foreach ($from as $key => $value) {
        // Elimina el prefijo, ignorando mayúsculas y minúsculas
        $newKey = preg_replace('/'.$prefix.'/i', '', $key);
        // Agrega la nueva clave y su valor al resultado
        $result[$newKey] = $value;
    }
    return $result;
}
function getURIParams(){return array_slice(explode("/", trim($_SERVER['REQUEST_URI'], '/')), 2);}