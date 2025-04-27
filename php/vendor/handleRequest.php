<?php
function handleRequest(): bool {
    $name = $_SERVER['REQUEST_METHOD'];
    if (function_exists($name))
        $name();
    else return false;
    return true;
}
function badRequest(): void {
    echo new Packet(ResponseCode::ERROR, "bad request method");
}