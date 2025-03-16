<?php
function checkNullValues($data, ...$keys) {
    foreach ($keys as $key) {
        if (!isset($data[$key])) {
            return true;
        }
    }
    return false;
}
?>