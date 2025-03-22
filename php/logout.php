<?php
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo "bad request method";
    exit();
}
session_start();

unset($_SESSION["TOKEN"]);
echo "succes";
