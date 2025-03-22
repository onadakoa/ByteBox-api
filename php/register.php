<?php
require_once 'autoload.php';
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "Bad request method";
    exit();
}

if (empty($_POST['login']) || empty($_POST['password'])) {
    echo "Bad request";
    exit();
}
session_start();
if (isset($_SESSION['TOKEN'])) {
    echo "already logged in";
    exit();
}

$db = get_mysqli();

$login = $_POST['login'];
$password = $_POST['password'];

$res = $db->query("SELECT user_id from user where login='$login'");
if ($res->num_rows > 0) {
    echo "login already occupied";
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);
$token = $password . $password_hash;

$query = "insert into user(login, password, first_name, last_name, persmission, token) value (?,?,'adam','rose',0, ?)";
$stmt = $db->prepare($query);
$stmt->bind_param("sss", $login, $password_hash, $token);
$stmt->execute();

$_SESSION['TOKEN'] = $token;

echo "Success";
exit();