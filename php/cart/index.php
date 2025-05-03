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

function POST() { // {user_id, product_id, quantity}
    useJson();

    $required = ["user_id", "product_id", "quantity"];
    $body = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("$field not specified", 400);
        if (!ctype_digit($_POST[$field])) badRequestJson("bad $field", 400);
        $body[$field] = $_POST[$field];
    }

    $db = get_mysqli();

    try {
        $stmt= $db->prepare("insert into cart_item(user_id, product_id, quantity) VALUE (?, ?, ?)");
        $stmt->bind_param("iii", $body['user_id'], $body['product_id'], $body['quantity']);
        $res = $stmt->execute();
        if (!$res) badRequestJson("error", 500);
        echo new Packet(ResponseCode::SUCCESS, ["id" => $db->insert_id]);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error {$e->getMessage()}", 500);
    }

    $db->close();
}

function PUT() { // {id, quantity}
    useJson();

    $body = [];
    parse_str(file_get_contents("php://input"), $body);

    $required = ["id", "quantity"];
    foreach ($required as $field) {
        if (!isset($body[$field])) badRequestJson("$field not specified", 400);
        if (!ctype_digit($body[$field])) badRequestJson("bad $field", 400);
    }

    $db = get_mysqli();

    $item = CartItem::fetch_cart_item($db, $body['id']);
    if (!$item) badRequestJson("not found");

    if (!$item->update($db, $body['quantity'])) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

function DELETE() {
    useJson();

    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) badRequestJson("bad id", 400);
    $id = $_GET['id'];

    $db =  get_mysqli();

    $item = CartItem::fetch_cart_item($db, $id);
    if (!$item) badRequestJson("not found");
    if (!$item->delete($db)) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

handleRequest();