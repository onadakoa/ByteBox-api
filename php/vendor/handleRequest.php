<?php
function handleRequest(): bool {
    $name = $_SERVER['REQUEST_METHOD'];
    if (function_exists($name))
        $name();
    else return false;
    return true;
}
function badRequest(string $description, int $code = 404) {
    http_response_code($code);
    header("X-Error: $description");

    exit();
}
