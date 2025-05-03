<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();
    $headers = getallheaders();

    $token = null;
    if (isset($_SESSION['token'])) $token = $_SESSION['TOKEN'];
    else if (isset($headers['TOKEN'])) $token = $headers['TOKEN'];
    if (!$token) badRequestJson("no auth", 400);

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 400);

    $address = ShippingAddress::fetch_all($db, $user->user_id);
    if (!$address) badRequestJson("not found");

    echo new Packet(ResponseCode::SUCCESS, $address);
    $db->close();
}

handleRequest();