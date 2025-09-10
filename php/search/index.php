<?php
require_once "../autoload.php";

function GET()
{
    session_start();
    useJson();
    $body = $_GET;

    $db = get_mysqli();

    if (!isset($body['search'])) badRequestJson("missing field search", 400);
    $phraze = trim(strtolower($body['search'] ?? ""));

    $query = <<<sql
    (select product_id as id, name, "product" as type from product where name like ?)
    union
    (select category_id as id, name, "category" as type from category where name like ?)
    union
    (select category_id as id, alias as name, "category" as type from category_alias where alias like ?)
    sql;

    $phraze = "%$phraze%";

    $stmt = $db->prepare($query);
    $stmt->bind_param("sss", $phraze, $phraze, $phraze);
    $stmt->execute();

    $res = $stmt->get_result();
    if (!$res) badRequestJson("error", 500);

    echo new Packet(ResponseCode::SUCCESS, $res->fetch_all(MYSQLI_ASSOC));
}

handleRequest();
