<?php

header("Access-Control-Allow-Origin: " . ($_SERVER['HTTP_ORIGIN'] ?? "http://localhost:3000"));
header("Access-Control-Allow-Credentials: true");


spl_autoload_register(function ($className) {
    $file = __DIR__ . '/vendor/' . str_replace("\\", "/", $className) . '.php';
    if (file_exists($file))
        require_once $file;
});
require_once "vendor/handleRequest.php";

function get_mysqli(): mysqli {
    return new mysqli("mysql", "root", "", "bytebox");
}

enum RequestMethod: int
{
    case POST = 1;
    case GET = 2;
    case OTHER = 8;
}

/**
 * @deprecated use `handleRequest()` method
 */
function use_request_method(int $method, $error = "bad request method") {
    $m = $_SERVER["REQUEST_METHOD"];
    $actual_request_method = RequestMethod::OTHER;

    if ($m == "GET") $actual_request_method = RequestMethod::GET;
    if ($m == "POST") $actual_request_method = RequestMethod::POST;

    if ($method & $actual_request_method->value) {
        return true;
    }
    if ($error != null) {echo new Packet(ResponseCode::ERROR, $error); exit();};
    return false;
}