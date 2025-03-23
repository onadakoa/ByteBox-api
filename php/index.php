<?php
require_once "autoload.php";
session_start();
use_request_method(RequestMethod::GET->value | RequestMethod::POST->value);
$db = new mysqli("mysql", "root", "", "bytebox");
$out = array();

if (isset($_SESSION['TOKEN'])) {
    $u = User::user_by_token($db, $_SESSION['TOKEN']);
    $out['user'] = $u;
} else
    $out['user'] = 0;


echo new Packet(ResponseCode::SUCCESS, $out);

$db->close();