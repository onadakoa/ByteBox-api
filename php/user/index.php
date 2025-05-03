<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();
    $token = useToken();
    if (!$token) badRequestJson("no auth", 400);

    $id = $_GET['id'] ?? -1;
    $search = $_GET['search'] ?? "";
    $limit = $_GET['limit'] ?? 20;
    $page = $_GET['page'] ?? 1;
    $offset = ($page - 1) * $limit;

    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("invalid token", 400);

    if ($id != -1) {
        $user = User::user_by_id($db, $id);
        if (!$user) badRequestJson("not found");
        echo new Packet(ResponseCode::SUCCESS, $user);
    } else if (isset($_GET['search'])) {
        if ($author->permission == 0) badRequestJson("no permission", 400);
        $users = User::user_by_search($db, $search, $limit, $offset);
        if (!$users) badRequestJson("not found");
        echo new Packet(ResponseCode::SUCCESS, $users);
    } else {
        echo new Packet(ResponseCode::SUCCESS, $author);
    }

    $db->close();
}

handleRequest();