<?php
require_once "../autoload.php";
use_request_method(RequestMethod::GET->value);

$db = get_mysqli();

$out = [];
$res = $db->query("select c.category_id, c.name, a.alias_id, a.alias from category c left join category_alias a on c.category_id=a.category_id order by c.category_id");

while ($row = $res->fetch_assoc()) {
    if (!isset($out[$row['category_id']])) {
       $out[$row['category_id']] = array(
          'category_id' => $row['category_id'],
          'name' => $row['name'],
          'alias' => []
       );
    }

    if ($row['alias']) {
        $out[$row['category_id']]['alias'][] = array(
            'alias_id' => $row['alias_id'],
            'alias' => $row['alias'],
        );
    }
}

echo new Packet(ResponseCode::SUCCESS, array_values($out));