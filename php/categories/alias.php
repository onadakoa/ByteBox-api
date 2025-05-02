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
    $db->close();
}

function POST() { // {alias, category_id?}
    useJson();
    if (!isset($_POST['alias'])) badRequestJson("bad request", 400);
    $alias = $_POST['alias'];
    if (isset($_POST['category_id']) && !ctype_digit($_POST['category_id'])) badRequestJson("bad request", 400);
    $category_id = $_POST['category_id'] ?? null;

    $db = get_mysqli();
    try {
        $stm = $db->prepare("insert into category_alias (category_id, alias) value (?, ?)");
        $stm->bind_param("is", $category_id, $alias);
        if (!$stm->execute()) badRequestJson("error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error, {$e->getMessage()}", 500);
    }

    echo new Packet(ResponseCode::SUCCESS, ["id" => $db->insert_id]);
    $db->close();
}

function PUT() { // {alias, category_id}
    useJson();
    $body = [];
    parse_str(file_get_contents("php://input"), $body);
    $id = $body['id'] ?? -1;
    if ($id < 0) badRequestJson("bad id", 400);

    if (!isset($body['alias'])) badRequestJson("no alias", 400);
    $alias = $body['alias'];
    if (!isset($body['category_id'])) badRequestJson("no category_id", 400);
    $category_id = $body['category_id'];

    $db = get_mysqli();
    try {
        $stm = $db->prepare("update category_alias set alias=?, category_id=? where alias_id=?");
        $stm->bind_param("sii", $alias, $category_id, $id);
        if (!$stm->execute()) badRequestJson("error", 500);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error, {$e->getMessage()}", 500);
    }

    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

handleRequest();