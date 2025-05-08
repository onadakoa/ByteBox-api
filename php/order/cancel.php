<?php
require_once "../autoload.php";

function GET() { // {id}
    session_start();
    useJson();
    $token = useToken();

    $id = $_GET["id"] ?? -1;
    if ($id==-1 || !is_numeric($id)) badRequestJson("bad id", 400);

    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("no auth", 401);
    $order = Order::fetch_by_id($db, $id);
    if (!$order) badRequestJson("not found");

    if ($author->permission==0 && $author->user_id!=$order->user_id) badRequestJson("no auth", 401);
    if (!$order->update_status($db, OrderStatus::canceled)) badRequestJson("error", 500);

    $order->refresh($db);
    echo new Packet(ResponseCode::SUCCESS, $order);
    $db->close();
}

handleRequest();