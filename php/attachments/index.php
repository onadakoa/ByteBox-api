<?php
require_once "../autoload.php";
session_start();
function GET() {
    $db = get_mysqli();
    $id = (int) ($_GET['id'] ?? -1);
    if ($id < 0) {
        if (!isset($_SESSION['TOKEN'])) {
            echo new Packet(ResponseCode::ERROR, "no auth");
            exit();
        }
        if (User::user_by_token($db, $_SESSION['TOKEN'])->permission != 1) {
            echo new Packet(ResponseCode::ERROR, "no auth");
            exit();
        }

        $limit = $_GET['limit'] ?? 10;
        $offset = (($_GET['page'] ?? 1) - 1) * $limit;

        $att = Attachment::fetch_all($db, $limit, $offset);
        echo new Packet(ResponseCode::SUCCESS, $att);
        exit();
    }

    $att = Attachment::fetch_attachment($db, $id);

    echo new Packet(ResponseCode::SUCCESS, $att);
    exit();
}

function POST() {
    header("Content-Type: application/json");

    $db = get_mysqli();
    $token = $_SESSION['TOKEN'] ?? -1;
    $user = User::user_by_token($db, $token);
    if (!$user || $user->permission == 0) badRequestJson("no auth", 400);

    $file_count = $_POST['file_count'] ?? 0;
    if ($file_count < 1 || $file_count > MAX_ATTACHMENT_FILES) badRequestJson("wrong file_count", 400);

    $files = json_decode($_POST['files'] ?? null, true); // [{file_id, size, type}]
    if (!$files || !is_array($files)) badRequestJson("wrong files", 400);

    foreach ($files as $f) {
        $id = $f['file_id'] ?? -1;
        $size = $f['size'] ?? 0;
        $type = $f['type'] ?? null;
        if (!in_array($type, ALLOW_MIME)) badRequestJson("file id=$id, wrong mimetype", 400);
        if ($size > MAX_SIZE) badRequestJson("file id=$id, size > MAX_SIZE", 400);
    }

    $query = "insert into attachment (author_id, image_count) values (4, $file_count)";
    $res = $db->query($query);
    if (!$res) badRequestJson("error", 500);
    $rowID = $db->insert_id;

    $out = [];

    foreach ($files as $f) {
        $query = "insert into image (attachment_id) values ($rowID)";
        $res = $db->query($query);
        if (!$res) badRequestJson("error", 500);
        $out[] = [
            "file_id" => ($f['id'] ?? -1),
            "id" => $db->insert_id
        ];
    }

    $db->close();
    echo new Packet(ResponseCode::SUCCESS, [
        "files" => $out,
        "attachment_id" => $rowID,
    ]);
}

handleRequest();