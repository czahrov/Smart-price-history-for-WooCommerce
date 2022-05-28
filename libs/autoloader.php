<?php
/**
 * PHP class autoloader
 * 
 * @param string $class_name
 * class name to load like: \path\to\class\file\class_name(.php)
 */
spl_autoload_register(function (string $class_name) {
    $class_path = str_replace('\\', '/', ltrim($class_name, '\\'));
    $file_path = __DIR__ . "/{$class_path}.php";

    if (file_exists($file_path)) {

        require $file_path;
        return true;
    }

    return false;
});
