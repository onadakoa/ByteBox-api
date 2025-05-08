<?php
require_once "../autoload.php";

function POST() { // {shipping_address_id, provider_id}
    session_start();
    useJson();
    $token = useToken();

    if (!isset($_POST['shipping_address_id']) || !is_numeric($_POST['shipping_address_id'])) badRequestJson("bad shipping_address_id", 400);
    $shipping_address_id = $_POST['shipping_address_id'];

    if (!isset($_POST['provider_id']) || !is_numeric($_POST['provider_id'])) badRequestJson("bad provider_id", 400);
    $provider_id = $_POST['provider_id'];

    $db = get_mysqli();

    $address = ShippingAddress::fetch_by_id($db, $shipping_address_id);
    if (!$address) badRequestJson("bad shipping_address", 400);

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("bad auth", 400);

    if ($user->user_id != $address->user_id) badRequestJson("bad auth", 400);

    $order = Order::insert_from_cart($db, $user->user_id, $shipping_address_id, $provider_id);

    echo new Packet(ResponseCode::SUCCESS, $order);
}

handleRequest();