<?php
require_once "../autoload.php";

$ALLOW_MIME = ["png", "jpg", "jpeg"];

$ALLOW_MIME = array_map(function ($val) {
    return "image/$val";
}, $ALLOW_MIME);

function checkDirs($path) {
    if (!file_exists($path) || !is_dir($path)) {
        mkdir($path);
    }
    return realpath($path);
}
$PATH = checkDirs("./upload");

function GET() {
    global $PATH;
    $db = get_mysqli();

    $id = (int) ($_GET["id"] ?? -1);
    if ($id < 0) badRequest("bad request");

    $img = Image::fetch_image($db, $id);
    if (!$img) badRequest("not found");

    if (!file_exists($PATH . $img->getPath())) badRequest("file error", 500);
    if (!is_file($PATH . $img->getPath())) badRequest("file error", 500);

    header("Content-Type: " . $img->type);
    header("Content-Length: " . $img->size);
    readfile($PATH . $img->getPath());
    $db->close();
}

function PUT() {
    global $PATH, $ALLOW_MIME;
    session_start();

    $id = (int) $_GET['id'] ?? -1;
    if ($id < 0) badRequestJson("no id specified", 400);
    $content = file_get_contents("php://input");
    $size = $_SERVER['CONTENT_LENGTH'];
    if ($content === false || $size == 0) badRequestJson("wrong body", 400);

    $db = get_mysqli();

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type = $finfo->buffer($content);
    finfo_close($finfo);
    if (!$type) badRequestJson("file error", 500);
    if (!in_array($type, $ALLOW_MIME)) badRequestJson("wrong file type", 400);

    $image = Image::fetch_image($db, $id);
    if (!$image) badRequestJson("not found");

    $newPath = "/" . date("d_m_Y") . "_" . bin2hex(random_bytes(8)) . ".image";

    if (!file_put_contents($PATH . $newPath, $content)) badRequestJson("file write error", 500);
    $image->update($newPath, $type, $size);

    echo new Packet(ResponseCode::SUCCESS, "success update");
    $db->close();
}

handleRequest();