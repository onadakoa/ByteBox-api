<?php
require_once "../autoload.php";

function GET() { // {user_id, id}
    useJson();

    if (isset($_GET['user_id']) && !ctype_digit($_GET['user_id'])) badRequestJson("bad user_id", 400);
    $user_id = $_GET['user_id'] ?? -1;
    if (isset($_GET['id']) && !ctype_digit($_GET['id'])) badRequestJson("bad id", 400);
    $id = $_GET['id'] ?? -1;

    $db = get_mysqli();

    if ($user_id != -1) {
        $user = User::user_by_id($db, $user_id);
        if (!$user) badRequestJson("not found");

        $items = CartItem::fetch_by_user_id($db, $user_id);

        echo new Packet(ResponseCode::SUCCESS, $items);
    } else if ($id != -1) {
       $item = CartItem::fetch_cart_item($db, $id);
       if (!$item) badRequestJson("not found");

       echo new Packet(ResponseCode::SUCCESS, $item);
    }

    $db->close();
}


handleRequest();