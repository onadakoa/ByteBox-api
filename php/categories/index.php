<?php
require_once "../autoload.php";

function GET()
{
    useJson();
    if (isset($_GET["id"]) && !ctype_digit($_GET['id'])) badRequestJson("bad id");
    $id = $_GET["id"] ?? -1;

    $search = $_GET["search"] ?? null;

    $db = get_mysqli();

    if ($id == -1) {
        $cats = Category::fetch_all($db, search: $search??"");
        if (!is_array($cats) && !$cats) badRequestJson("error", 500);
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

function PUT() { // {name, id}
    useJson();
    $body = [];
    parse_str(file_get_contents("php://input"), $body);

    if (!isset($body['id']) || !isset($body['name'])) badRequestJson("bad request", 400);
    $id = $body['id'];
    $name = $body['name'];

    $db = get_mysqli();
    try {
        $stm = $db->prepare("update category set name=? where category_id=?");
        $stm->bind_param("si", $name, $id);
        if (!$stm->execute()) badRequestJson("error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error", 500);
    }

    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

function DELETE() {
    $id = $_GET["id"] ?? -1;
    if ($id < 0 || !ctype_digit($id)) badRequestJson("bad request", 400);

    $db = get_mysqli();
    $res = $db->query("delete from category where category_id=$id");
    if (!$res) badRequestJson("error", 500);

    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

handleRequest();