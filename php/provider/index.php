<?php
require_once "../autoload.php";

function GET() {
    useJson();

    $db = get_mysqli();

    $providers = Provider::fetch_all($db);

    echo new Packet(ResponseCode::SUCCESS, $providers);
}

handleRequest();