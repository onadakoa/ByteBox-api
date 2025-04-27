<?php
require_once "../autoload.php";

function GET() {
    $limit = (int) ($_GET['limit'] ?? 10);
    $page = (int) ($_GET['page'] ?? 1);
    $offset = ($page - 1) * $limit;

    $query = "select * from product";

    if (isset($_GET['id'])) {
        $query .= " where product_id={$_GET['id']}";
    } else $query .= " limit $limit offset $offset";

    $db = get_mysqli();

    $res = $db->query($query);
    if ($res->num_rows == 0) {
        echo new Packet(ResponseCode::ERROR, "No products found.");
        exit();
    }

    $rows = $res->fetch_all(MYSQLI_ASSOC);

    if (count($rows) == 1) echo new Packet(ResponseCode::SUCCESS, $rows[0]);
    else echo new Packet(ResponseCode::SUCCESS, $rows);
}

handleRequest();