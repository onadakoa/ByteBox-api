<?php
require_once "../autoload.php";

function GET() {
    useJson();
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) badRequestJson("no id", 400);
    $id = $_GET['id'];
    $db = get_mysqli();
    $res = $db->query("select * from category_alias where alias_id=$id");
    if (!$res) badRequestJson("error", 500);

    $row = $res->fetch_assoc();
    echo new Packet(ResponseCode::SUCCESS, $row);
}

handleRequest();