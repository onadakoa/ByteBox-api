<?php

class CartData
{
    public float $total_price;
    public int $count;
    public int $total_quantity;


    public static function fetch(mysqli $db, int $user_id): CartData|false {
        $query = <<<sql
        select 
        sum(p.price * ci.quantity) as total_price, 
        count(*) as count,
        sum(ci.quantity) as total_quantity
        from cart_item ci
        join product p on ci.product_id = p.product_id
        where user_id={$user_id} 
        sql;
        $res = $db->query($query);
        if (!$res) return false;
        return $res->fetch_object("CartData");
    }
}