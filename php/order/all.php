<?php
require_once "../autoload.php";

function GET() { // {limit?, page?}
    session_start();
    useJson();
    $token = useToken();

    $limit = $_GET['limit'] ?? 20;
    $page = $_GET['page'] ?? 1;
    if (!is_numeric($limit) || !is_numeric($page)) badRequestJson("bad request", 400);
    $offset = ($page - 1) * $limit;

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user || $user->permission==0) badRequestJson("no auth", 400);

    $orders = Order::fetch_all($db, $limit, $offset);
    if (!$orders) badRequestJson("error" ,500);

    echo new Packet(ResponseCode::SUCCESS, $orders);
    $db->close();
}

handleRequest();