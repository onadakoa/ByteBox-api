<?php
require_once 'autoload.php';

function POST() { // {login, password, first_name, last_name}
    session_start();
    useJson();

    $token = useToken(false);
    if ($token) badRequestJson("already logged in", 400);

    $required = ["login", "password", "first_name", "last_name"];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("no $field specified", 400);
    }

    $db = get_mysqli();
    $user = User::insert_new($db, $_POST['login'], $_POST['password'], ["first_name"=>$_POST['first_name'], "last_name"=>$_POST['last_name']]);
    if (!$user) badRequestJson("error", 500);

    $_SESSION['TOKEN'] = $user->getToken();

    echo new Packet(ResponseCode::SUCCESS, $user);
    $db->close();
}

handleRequest();