<?php
require_once "../autoload.php";

function GET() {
    useJson();

    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $id = (int) ($_GET['id'] ?? -1);
    $search = ($_GET['search'] ?? "");
    $category = (int) ($_GET['category'] ?? -1);
    $price_in = (int) ($_GET['price_in'] ?? 0);
    $price_out = (int) ($_GET['price_out'] ?? 0);
    $sort = ($_GET['sort'] ?? "P_ASC");

    $db = get_mysqli();

    if ($id==-1) {
        $products = Product::fetch_all($db, $search, $limit, $offset, $category, price_in: $price_in, price_out: $price_out, sort: $sort);
        if (!$products) badRequestJson("not found", 500);
        echo new Packet(ResponseCode::SUCCESS, $products);
    }
    else {
        $product = Product::fetch_by_id($db, $id);
        if (!$product) badRequestJson("not found", 404);
        echo new Packet(ResponseCode::SUCCESS, $product);
    }

    $db->close();
}

function POST() { // {name, description, attachment_id?, price, stock, category_id}
    session_start();
    useJson();
    $token = useToken();

    $required = ["name", "description", "price", "stock", "category_id"];
    $obj = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("missing field $field", 400);
        $obj[$field] = $_POST[$field];
    }
    $obj['attachment_id'] = $_POST['attachment_id'] ?? null;

    $db = get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 401);

    $query = <<<sql
    insert into product (name, description, attachment_id, author_id, price, stock, category_id)
    value (?, ?, ?, ?, ?, ?, ?)
    sql;

    $stmt = $db->prepare($query);

    $stmt->bind_param("ssiidii",
    $obj['name'], $obj['description'], $obj['attachment_id'], $user->user_id, $obj['price'], $obj['stock'], $obj['category_id']
    );

    try {
        if (!$stmt->execute()) badRequestJson("server error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("server error, {$e->getMessage()}", 500);
    }

    $nId = $db->insert_id;

    echo new Packet(ResponseCode::SUCCESS, ["id" => $nId]);

    $db->close();
}

function PUT() {
    session_start();
    useJson();
    $required = ["name", "description", "price", "stock", "category_id", "id"];
    $obj = [];
    $body = useJsonData();
    foreach ($required as $field) {
        if (!isset($body[$field])) badRequestJson("missing field $field", 400);
        $obj[$field] = $body[$field];
    }
    $obj['attachment_id'] = $body['attachment_id'] ?? null;

    $db = get_mysqli();

    try {
        $stmt = $db->prepare("update product set name=?, description=?, price=?, stock=?, category_id=?, attachment_id=? where product_id=?");
        $stmt->bind_param("ssdiiii", $obj['name'], $obj['description'], $obj['price'], $obj['stock'], $obj['category_id'], $obj['attachment_id'], $obj['id']);

        if (!$stmt->execute()) badRequestJson("server error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("server error, {$e->getMessage()}", 500);
    }

    echo new Packet(ResponseCode::SUCCESS, ["id" => $obj['id']]);
    $db->close();
}

function DELETE() {
    session_start();
    useJson();
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) badRequestJson("id incorrect", 400);
    $id = (int) ($_GET['id'] ?? -1);
    if ($id < 0) badRequestJson("bad id", 400);

    $db = get_mysqli();

    try {
        $stmt = $db->prepare("delete from product where product_id=?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) badRequestJson("server error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("server error, {$e->getMessage()}", 500);
    }

    echo new Packet(ResponseCode::SUCCESS);
}

handleRequest();