<?php

class CartItem
{
    public int $id;
    public int $user_id;
    public int $product_id;
    public int $quantity;

    private mysqli $db;

    public function __construct(mysqli $db, int $id, int $user_id, int $product_id, int $quantity)
    {
        $this->db = $db;
        $this->id = $id;
        $this->user_id = $user_id;
        $this->product_id = $product_id;
        $this->quantity = $quantity;
    }

    public static function fetch_cart_item(mysqli $db, int $id): CartItem|false
    {
        $query = "select * from cart_item where cart_item_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1)
            return false;
        $row = $result->fetch_assoc();

        return new CartItem($db, $row['cart_item_id'], $row['user_id'], $row['product_id'], $row['quantity']);
    }

    public function fetch_user(): User|false
    {
        return User::user_by_id($this->db, $this->user_id);
    }

    public function fetch_product(): Product|false
    {
        return Product::fetch_by_id($this->db, $this->product_id);
    }


}