<?php
require_once "../autoload.php";

function GET()
{
    useJson();
    if (isset($_GET["id"]) && !ctype_digit($_GET['id'])) badRequestJson("bad id");
    $id = $_GET["id"] ?? -1;

    $db = get_mysqli();

    if ($id == -1) {
        $cats = Category::fetch_all($db);
        if (!$cats) badRequestJson("error", 500);
        echo new Packet(ResponseCode::SUCCESS, $cats);
        $db->close();
        return;
    }
    $cat = Category::fetch_category($db, $id);
    if (!$cat) badRequestJson("not found", 404);
    echo new Packet(ResponseCode::SUCCESS, $cat);
    $db->close();
}

handleRequest();