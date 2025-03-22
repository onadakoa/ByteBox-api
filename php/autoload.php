<?php

header("Access-Control-Allow-Origin: localhost");


spl_autoload_register(function ($className) {
    $file = __DIR__ . '/vendor/' . str_replace("\\", "/", $className) . '.php';
    if (file_exists($file))
        require_once $file;
});

function get_mysqli(): mysqli {
    return new mysqli("mysql", "root", "", "bytebox");
}