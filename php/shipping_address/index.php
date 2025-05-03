<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();
    $headers = getallheaders();

    $token = null;
    if (isset($_SESSION['token'])) $token = $_SESSION['TOKEN'];
    else if (isset($headers['TOKEN'])) $token = $headers['TOKEN'];
    if (!$token) badRequestJson("no auth", 400);

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 400);

    $address = ShippingAddress::fetch_all($db, $user->user_id);
    if (!$address) badRequestJson("not found");

    echo new Packet(ResponseCode::SUCCESS, $address);
    $db->close();
}

function POST() {
    session_start();
    useJson();
    $headers = getallheaders();

    $token = null;
    if (isset($_SESSION['token'])) $token = $_SESSION['TOKEN'];
    else if (isset($headers['TOKEN'])) $token = $headers['TOKEN'];
    if (!$token) badRequestJson("no auth", 400);

    $required = [
        "building_number",
        "city",
        "first_name",
        "last_name",
        "phone_number",
        "postal_code",
        "street",
    ];
    foreach ($required as $field) {
        if (!isset($_POST[$field])) badRequestJson("missing $field", 400);
    }
    $apartment_number = $_POST["apartment_number"] ?? null;

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 400);

    try {
    $stmt = $db->prepare("insert into shipping_address (user_id, first_name, last_name, phone_number, postal_code, city, street, building_number, apartment_number)
    values (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issssssss",
        $user->user_id,
        $_POST["first_name"],
        $_POST["last_name"],
        $_POST["phone_number"],
        $_POST["postal_code"],
        $_POST["city"],
        $_POST["street"],
        $_POST["building_number"],
        $apartment_number
    );
    if (!$stmt->execute()) badRequestJson("error", 500);

    echo new Packet(ResponseCode::SUCCESS, ["id"=>$db->insert_id]);
    } catch (mysqli_sql_exception $e) {
        badRequestJson("error {$e->getMessage()}", 500);
    }

    $db->close();
}

handleRequest();