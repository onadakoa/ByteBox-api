<?php
require_once "../autoload.php";

function GET() { // {product_id?}
    session_start();
    useJson();
    $token = useToken();

    if (isset($_GET['product_id']) && !ctype_digit($_GET['product_id'])) badRequestJson("bad id", 400);
    $id = $_GET['product_id'] ?? -1;

    $db = get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("bad token", 400);

    if ($id == -1) {
        $items = CartItem::fetch_by_user_id($db, $user->user_id);
        $data = CartData::fetch($db, $user->user_id);

        echo new Packet(ResponseCode::SUCCESS, [
            "items" => $items,
            "data" => $data
        ]);
    } else {
       $item = CartItem::fetch_by_product_id($db, $user->user_id, $id);
       if (!$item) badRequestJson("not found");

       echo new Packet(ResponseCode::SUCCESS, $item);
    }

    $db->close();
}

function POST() { // {product_id, quantity}
    session_start();
    useJson();
    $token = useToken();

    $required = ["product_id", "quantity"];
    $body = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("$field not specified", 400);
        if (!is_numeric($_POST[$field])) badRequestJson("bad $field", 400);
        $body[$field] = $_POST[$field];
    }

    $db = get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("bad token", 400);

    $item = CartItem::fetch_by_product_id($db, $user->user_id, $body["product_id"]);
    if (!$item) {
        try {
            $stmt = $db->prepare("insert into cart_item(user_id, product_id, quantity) VALUE (?, ?, ?)");
            $quantity = $body["quantity"];
            if ($quantity <= 0) badRequestJson("bad quantity", 400);
            $stmt->bind_param("iii", $user->user_id, $body['product_id'], $quantity);
            $res = $stmt->execute();
            if (!$res) badRequestJson("error", 500);
            echo new Packet(ResponseCode::SUCCESS, ["id" => $db->insert_id]);
        } catch (mysqli_sql_exception $e) {
            badRequestJson("error {$e->getMessage()}", 500);
        }
    } else {
        if (($item->quantity + $body['quantity']) <= 0) {
           $item->delete($db);
           echo new Packet(ResponseCode::SUCCESS, ['id' => -1]);
        } else {
            $item->update($db, $item->quantity+$body["quantity"]);
            echo new Packet(ResponseCode::SUCCESS, ["id" => $item->cart_item_id]);
        }
    }

    $db->close();
}

function PUT() { // {product_id, quantity}
    session_start();
    useJson();
    $token = useToken();

    $body = useJsonData();

    $required = ["product_id", "quantity"];
    foreach ($required as $field) {
        if (!isset($body[$field])) badRequestJson("$field not specified", 400);
        if (!ctype_digit((string)$body[$field])) badRequestJson("bad $field", 400);
    }

    $db = get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("bad token", 400);

    $item = CartItem::fetch_by_product_id($db, $user->user_id, $body["product_id"]);
    if (!$item) badRequestJson("not found");

    if (!$item->update($db, $body['quantity'])) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

function DELETE() {
    session_start();
    useJson();
    $token = useToken();

    $id = $_GET['id'] ?? -1;
    if (!is_numeric($id)) badRequestJson("bad id", 400);
    $product_id = $_GET['product_id'] ?? -1;
    if (!is_numeric($product_id)) badRequestJson("bad product_id", 400);

    $db =  get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$token) badRequestJson("bad token", 400);

    $item = null;
    if ($product_id != -1)
        $item = CartItem::fetch_by_product_id($db, $user->user_id, $product_id);
    else if ($id != -1)
        $item = CartItem::fetch_cart_item($db, $id);
    else {
        $res = $db->query("delete from cart_item where user_id = {$user->user_id}");
        if (!$res) badRequestJson("error", 500);
        echo new Packet(ResponseCode::SUCCESS);
        $db->close();
        exit();
    }

    if (!$item) badRequestJson("not found");
    if (!$item->delete($db)) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

handleRequest();