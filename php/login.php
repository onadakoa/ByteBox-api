<?php
require_once 'autoload.php';
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "Invalid request method";
    exit();
}
session_start();
/*
 * what i'm expecting?
 * POST{
 * login: prob email example@example.com
 * password: raw password (will be hashed)
 * }
 */
if (isset($_SESSION['TOKEN'])) {
    echo "Already logged in";
    exit();
}
if (empty($_POST['login']) || empty($_POST['password'])) {
    echo "Invalid request";
    print_r($_POST);
    exit();
}

$db = get_mysqli();

$login = $_POST['login'];
$password = $_POST['password'];

$res = $db->query("SELECT password, token FROM user WHERE login = '$login'");
if ($res->num_rows == 0) {
    echo "User not found";
    exit();
}
$row = $res->fetch_assoc();
if (!password_verify($password, $row['password'])) {
    echo "Wrong password";
    exit();
}

$_SESSION['TOKEN'] = $row['token'];
echo "Successfully logged in";

$db->close();