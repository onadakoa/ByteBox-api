<?php
require_once "../autoload.php";

function GET() {
    session_start();
    useJson();
    $headers = getallheaders();

    $token = useToken();

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 401);

    $address = ShippingAddress::fetch_all($db, $user->user_id);
    if (!$address && !is_array($address)) badRequestJson("not found");

    echo new Packet(ResponseCode::SUCCESS, $address);
    $db->close();
}

function POST() {
    session_start();
    useJson();

    $token = useToken();

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
    if (!$user) badRequestJson("no auth", 401);

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

function DELETE() {
    session_start();
    useJson();
    $token = useToken();

    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) badRequestJson("no id", 400);
    $id = $_GET['id'];

    $db = get_mysqli();
    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 401);
    $address = ShippingAddress::fetch_by_id($db, $id);
    if (!$address) badRequestJson("not found");
    if ($user->user_id != $address->user_id) badRequestJson("not found", 404);

    if (!$address->delete($db)) badRequestJson("error", 500);
    echo new Packet(ResponseCode::SUCCESS);
    $db->close();
}

function PUT() {
    session_start();
    useJson();
    $token = useToken();

    $body = useJsonData();

    $required = [
        "id",
        "building_number",
        "city",
        "first_name",
        "last_name",
        "phone_number",
        "postal_code",
        "street",
    ];
    foreach ($required as $field) {
        if (!isset($body[$field])) badRequestJson("missing $field", 400);
    }

    $db = get_mysqli();

    $user = User::user_by_token($db, $token);
    if (!$user) badRequestJson("no auth", 401);
    $address = ShippingAddress::fetch_by_id($db, $body['id']);
    if (!$address) badRequestJson("not found");

    if (!$address->update($db, $body)) badRequestJson("error", 500);
    $db->close();
    echo new Packet(ResponseCode::SUCCESS);
}

handleRequest();