<?php
require_once "../autoload.php";

function GET() {
    useJson();

    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;
    $id = (int) ($_GET['id'] ?? -1);

    $db = get_mysqli();

    if ($id==-1) {
        $products = Product::fetch_all($db, $limit, $offset);
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

function POST() { // {name, description, attachment_id?, author_id, price, stock, category_id}
    session_start();
    useJson();
    $required = ["name", "description", "author_id", "price", "stock", "category_id"];
    $obj = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("missing field $field", 400);
        $obj[$field] = $_POST[$field];
    }
    $obj['attachment_id'] = $_POST['attachment_id'] ?? null;

    $db = get_mysqli();

    $query = <<<sql
    insert into product (name, description, attachment_id , author_id, price, stock, category_id)
    value (?, ?, ?, ?, ?, ?, ?)
    sql;

    $stmt = $db->prepare($query);

    $stmt->bind_param("ssiidii",
    $obj['name'], $obj['description'], $obj['attachment_id'], $obj['author_id'], $obj['price'], $obj['stock'], $obj['category_id']
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

handleRequest();