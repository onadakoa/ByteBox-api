<?php
require_once "../autoload.php";

$limit = (int) ($_GET['limit'] ?? 5);
$page = (int) ($_GET['page'] ?? 1);

$db = get_mysqli();


$out = [];

$offset = ($page-1)*$limit;
$res = $db->query("select * from product limit $limit offset {$offset}");

while ($row = $res->fetch_assoc()) {
    $out[] = $row;
}


echo new Packet(ResponseCode::SUCCESS, $out);