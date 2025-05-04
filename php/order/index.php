<?php
require_once "../autoload.php";

function GET() { // {id?, user_id?}
    session_start();
    useJson();
    $token = useToken();

    $id = $_GET['id'] ?? -1;
    $user_id = $_GET['user_id'] ?? -1;
    if (!is_numeric($id) || !is_numeric($user_id)) badRequestJson("bad request", 400);

    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("no auth", 400);

    if ($id != -1) {
        $order = Order::fetch_by_id($db, $id);
        if (!$order) badRequestJson("not found");
        if ($author->permission==0 && $author->user_id != $order->user_id) badRequestJson("not found");
        echo new Packet(ResponseCode::SUCCESS, $order);
    } else if ($user_id != -1) {
        if ($author->permission==0 && $author->user_id != $user_id) badRequestJson("no auth", 400);
        $orders = Order::fetch_by_user_id($db, $user_id);
        if (!$orders) badRequestJson("not found");
        echo new Packet(ResponseCode::SUCCESS, $orders);
    } else {
        $orders = Order::fetch_by_user_id($db, $author->user_id);
        if (!$orders) badRequestJson("error", 500);
        echo new Packet(ResponseCode::SUCCESS, $orders);
    }

    $db->close();
}

function PUT() { // {id, status}
    session_start();
    useJson();
    $token = useToken();
    $body = useFormData();

    if (!isset($body['id']) || !is_numeric($body['id'])) badRequestJson("bad id", 400);
    $id = $body['id'];
    if (!isset($body['status'])) badRequestJson("no status", 400);
    $status = OrderStatus::tryFrom($body['status']);
    if (!$status) badRequestJson("bad status", 400);

    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author || $author->permission==0) badRequestJson("no auth", 400);
    $order = Order::fetch_by_id($db, $id);
    if (!$order) badRequestJson("not found");

    if (!$order->update_status($db, $status)) badRequestJson("error", 500);

    $order->refresh($db);
    echo new Packet(ResponseCode::SUCCESS, $order);
    $db->close();
}

handleRequest();