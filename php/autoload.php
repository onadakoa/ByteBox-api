<?php

header("Access-Control-Allow-Origin: *");


spl_autoload_register(function ($className) {
    $file = __DIR__ . '/vendor/' . str_replace("\\", "/", $className) . '.php';
    if (file_exists($file))
        require_once $file;
});