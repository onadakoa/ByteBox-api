<?php
require_once "autoload.php";

function GET() {
    session_start();
    $db = get_mysqli();
    $out = array();

    if (isset($_SESSION['TOKEN'])) {
        $u = User::user_by_token($db, $_SESSION['TOKEN']);
        $out['user'] = $u;
    } else
        $out['user'] = 0;


    echo new Packet(ResponseCode::SUCCESS, $out);

    $db->close();

}

handleRequest();