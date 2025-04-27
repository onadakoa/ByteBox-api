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

handleRequest();