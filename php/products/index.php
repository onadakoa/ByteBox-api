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

handleRequest();