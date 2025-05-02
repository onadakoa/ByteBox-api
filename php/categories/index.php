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

function POST() { // {name}
    useJson();
    if (!isset($_POST['name'])) badRequestJson("bad request", 400);
    $name = $_POST['name'];
    $db = get_mysqli();

    try {
        $stmt = $db->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if (!$stmt->execute()) badRequestJson("error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error {$e->getMessage()}", 500);
    }
    $nId = $db->insert_id;
    echo new Packet(ResponseCode::SUCCESS, ["id" => $nId]);
    $db->close();
}

handleRequest();