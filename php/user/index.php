<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();
    $token = useToken();

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
        if ($author->permission == 0) badRequestJson("no permission", 403);
        $users = User::user_by_search($db, $search, $limit, $offset);
        if (!$users) badRequestJson("not found");
        echo new Packet(ResponseCode::SUCCESS, $users);
    } else {
        echo new Packet(ResponseCode::SUCCESS, $author);
    }

    $db->close();
}

function PUT() { // {id, login, password, first_name, last_name, permission}
    session_start();
    useJson();
    $token = useToken();
    $body = useJsonData();

    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("invalid token", 401);

    $id = -1;
    if ($author->permission > 0) {
        $id = $_GET['id'] ?? $author->user_id;
    } else $id = $author->user_id;
    $target = User::user_by_id($db, $id);
    if (!$target) badRequestJson("not found");

    if (!$target->update($db, $body['login'] ?? null, $body['password'] ?? null, $body)) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS, $target);
    $db->close();
}

function DELETE() {
    session_start();
    useJson();
    $token = useToken();

    $id = $_GET['id'] ?? -1;
    $db = get_mysqli();
    $author = User::user_by_token($db, $token);
    if (!$author) badRequestJson("invalid token", 400);

    if ($id==-1) {
        if (!$author->delete($db)) badRequestJson("error", 500);
        unset($_SESSION['TOKEN']);
        echo new Packet(ResponseCode::SUCCESS);
        $db->close();
        exit();
    }

    if ($author->permission == 0) badRequestJson("no permission", 403);
    $target = User::user_by_id($db, $id);
    if (!$target) badRequestJson("not found");

    if (!$target->delete($db)) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

handleRequest();