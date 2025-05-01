<?php
require_once "../autoload.php";

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

    header("Content-Type: " . $img->type);
    header("Content-Length: " . $img->size);
    readfile($PATH . $img->getPath());
    $db->close();
}

function PUT() {
    global $PATH;
    session_start();

    $id = (int) $_GET['id'] ?? -1;
    if ($id < 0) badRequest("no id specified", 400);
    $content = file_get_contents("php://input");
    $size = $_SERVER['CONTENT_LENGTH'];
    if ($content === false || $size == 0) badRequest("wrong body", 400);

    $db = get_mysqli();

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $type = $finfo->buffer($content);
    finfo_close($finfo);

    $image = Image::fetch_image($db, $id);
    if (!$image) badRequest("not found");

    $newPath = "/" . date("d_m_Y") . "_" . bin2hex(random_bytes(8)) . ".image";

    if (!file_put_contents($PATH . $newPath, $content)) badRequest("file write error", 500);
    $image->update($newPath, $type, $size);

    echo new Packet(ResponseCode::SUCCESS, "success update");
    $db->close();
}

handleRequest();