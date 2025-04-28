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

    if (!file_exists($img->getPath())) badRequest("file error", 500);

    header("Content-Type: " . $img->type);
    readfile($img->getPath());
}


handleRequest();