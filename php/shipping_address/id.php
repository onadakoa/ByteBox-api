<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();

    $token = useToken();

    $id = $_GET['id'] ?? -1;
    if ($id < 1) badRequestJson("bad id", 400);

    $db = get_mysqli();

    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("no auth", 401);

    $address = ShippingAddress::fetch_by_id($db, $id);
    if (!$address) badRequestJson("not found");

    if ($author->user_id != $address->user_id) badRequestJson("not found");

    echo new Packet(ResponseCode::SUCCESS, $address);
}

handleRequest();