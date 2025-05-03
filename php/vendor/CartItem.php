<?php

class CartItem
{
    public int $cart_item_id;
    public int $user_id;
    public int $product_id;
    public int $quantity;


    public function fill(int $id, int $user_id, int $product_id, int $quantity)
    {
        $this->cart_item_id = $id;
        $this->user_id = $user_id;
        $this->product_id = $product_id;
        $this->quantity = $quantity;
    }

    public function update(mysqli $db, $quantity) {
       $query = "update cart_item set quantity=$quantity where cart_item_id={$this->cart_item_id}";
       return $db->query($query);
    }

    public static function fetch_cart_item(mysqli $db, int $id): CartItem|false
    {
        $query = "select * from cart_item where cart_item_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1)
            return false;

        return $result->fetch_object("CartItem");
    }

    /**
     * @return CartItem[]|false
     */
    public static function fetch_by_user_id(mysqli $db, int $user_id) {
        $query = "select * from cart_item where user_id=$user_id";
        $res = $db->query($query);
        if (!$res) return false;

        $out = [];
        while ($row = $res->fetch_object("CartItem")) {
            $out[] = $row;
        }

        return $out;
    }

    public function fetch_user(mysqli $db): User|false
    {
        return User::user_by_id($db, $this->user_id);
    }

    public function fetch_product(mysqli $db): Product|false
    {
        return Product::fetch_by_id($db, $this->product_id);
    }


}