<?php
require_once 'autoload.php';
use_request_method(RequestMethod::POST->value);

session_start();

if (isset($_SESSION['TOKEN'])) {
    echo new Packet(ResponseCode::ERROR, "Already logged in");
    exit();
}
if (empty($_POST['login']) || empty($_POST['password'])) {
    echo new Packet(ResponseCode::ERROR, "Invalid request");
    exit();
}

$db = get_mysqli();

$login = $_POST['login'];
$password = $_POST['password'];

$res = $db->query("SELECT password, token FROM user WHERE login = '$login'");
if ($res->num_rows == 0) {
    echo new Packet(ResponseCode::ERROR, "User not found");
    exit();
}
$row = $res->fetch_assoc();
if (!password_verify($password, $row['password'])) {
    echo new Packet(ResponseCode::ERROR, "Wrong password");
    exit();
}

$_SESSION['TOKEN'] = $row['token'];
echo new Packet(ResponseCode::SUCCESS, "Successfully logged in");

$db->close();