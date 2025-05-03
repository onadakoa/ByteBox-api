<?php
require_once 'autoload.php';

function POST() {
    session_start();
    useJson();

    $headers = getallheaders();

    $token = $_SESSION['TOKEN'] ?? $headers['TOKEN'] ?? -1;
    if ($token != -1) badRequestJson("already logged in", 400);

    $login = $_POST['login'] ?? -1;
    $password = $_POST['password'] ?? -1;
    if ($login == -1 || $password == -1) badRequestJson("bad request", 400);

    $db = get_mysqli();
    $user = User::user_by_credentials($db, $login, $password);
    if (!$user) badRequestJson("not found");

    $_SESSION['TOKEN'] = $user->getToken();

    echo new Packet(ResponseCode::SUCCESS, $user);
    $db->close();
}

handleRequest();