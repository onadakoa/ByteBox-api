<?php
require_once "autoload.php";
use_request_method(RequestMethod::POST->value);

session_start();

unset($_SESSION["TOKEN"]);
echo new Packet(ResponseCode::SUCCESS, "Success");
