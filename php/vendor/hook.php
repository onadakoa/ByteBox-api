<?php

function useJson() {
    header("Content-type: application/json");
}

function useToken(bool $strict = true) {
    $headers = getallheaders();

    $token = null;
    if (isset($_SESSION['token'])) $token = $_SESSION['TOKEN'];
    else if (isset($headers['TOKEN'])) $token = $headers['TOKEN'];
    if (!$token && $strict) badRequestJson("no auth", 400);

    return $token;
}