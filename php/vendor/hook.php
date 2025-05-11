<?php

function useJson() {
    header("Content-type: application/json");
}

function useToken(bool $strict = true) {
    $headers = getallheaders();

    $token = null;
    if (isset($_SESSION['TOKEN'])) $token = $_SESSION['TOKEN'];
    else if (isset($headers['TOKEN'])) $token = $headers['TOKEN'];
    if (!$token && $strict) badRequestJson("no auth", 401);

    return $token;
}

function useFormData(): array {
    $out = [];
    parse_str(file_get_contents("php://input"), $out);
    return $out;
}