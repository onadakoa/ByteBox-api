<?php
require_once "../autoload.php";
useJson();

$db = get_mysqli();

$query = <<<sql
select
p.*,
u.*,
o.*,
oi.*
from
 (select count(*) as product_count, round(avg(price), 2) as avg_product_price from product) as p,
 (select count(*) as user_count from user) as u,
 (select count(*) as order_count from `order`) as o,
 (select sum(price) as profit from `order_item`) as oi;
sql;

$res = $db->query($query);

$row  = $res->fetch_assoc();

echo new Packet(ResponseCode::SUCCESS, $row);

$db->close();