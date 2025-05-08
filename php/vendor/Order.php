<?php

enum OrderStatus : string
{
    case pending = "pending";
    case paid = "paid";
    case shipping = "shipping";
    case delivered = "delivered";
    case canceled = "canceled";
}

class OrderItem
{
    public int $order_item_id;
    public int $order_id;
    public int $product_id;
    public int $quantity;
    public float $price;
}

class Order
{
    public int $order_id;
    public int $user_id;
    public int|null $provider_id;
    public int $created_at;
    public string $status;

    public string|null $first_name;
    public string|null $last_name;
    public string|null $phone_number;
    public string|null $postal_code;
    public string|null $city;
    public string|null $street;
    public string|null $building_number;
    public string|null $apartment_number;

    public float|null $total_price;

    /**
     * @var OrderItem[]
     */
    public array $items = [];

    public function fill_items(mysqli $db): bool {
        $this->items = [];
        $res = $db->query("select * from order_item where order_id={$this->order_id}");
        if (!$res) return false;

        while ($row = $res->fetch_object("OrderItem")) {
            $this->items[] = $row;
        }
        return true;
    }

    public function refresh(mysqli $db): bool {
        $res = $db->query("select *, (select SUM(price) from order_item where order_id=o.order_id) as total_price, unix_timestamp(created_at) as created_at from `order` o where order_id={$this->order_id}");
        if (!$res) return false;
        $row = $res->fetch_assoc();

        foreach ($row as $k => $v) {
            if (!property_exists($this, $k)) continue;
            $this->$k = $v;
        }

        $this->fill_items($db);

        return true;
    }

    public function delete(mysqli $db): bool {
        return $db->query("delete from `order` where order_id={$this->order_id}");
    }

    public function update_status(mysqli $db, OrderStatus $status): bool {
        return $db->query("update `order` set status='{$status->value}' where order_id={$this->order_id}");
    }

    public function append_item(mysqli $db, int $product_id, float $price, int $quantity): bool {
        $res = $db->query("insert into order_item (order_id, product_id, price, quantity) value ({$this->order_id}, {$product_id}, {$price}, {$quantity})");
        return $res;
    }

    /**
     * @return Order[]|false
     */
    public static function fetch_by_user_id(mysqli $db, int $user_id): array|false {
        $user = User::user_by_id($db, $user_id);
        if (!$user) return false;

        $res = $db->query("select *, (select SUM(price) from order_item where order_id=o.order_id) as total_price, unix_timestamp(created_at) as created_at from `order` o where user_id={$user_id}");
        if (!$res) return false;

        $out = [];
        while ($row = $res->fetch_object("Order")) {
            $row->fill_items($db);
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @return Order|false
     */
    public static function fetch_by_id(mysqli $db, int $id): Order|false {
        $res = $db->query("select *, (select SUM(price) from order_item where order_id=o.order_id) as total_price, unix_timestamp(created_at) as created_at from `order` o where order_id={$id}");
        if (!$res) return false;

        $row = $res->fetch_object("Order");
        if (!$row) return false;
        $row->fill_items($db);

        return $row;
    }


    /**
     * @return Order[]|false
     */
    public static function fetch_all(mysqli $db, int $limit = 20, int $offset = 0): array|false {
        $res = $db->query("select *, (select SUM(price) from order_item where order_id=o.order_id) as total_price, unix_timestamp(created_at) as created_at from `order` o limit {$limit} offset {$offset}");
        if (!$res) return false;

        $out = [];
        while ($row = $res->fetch_object("Order")) {
            $row->fill_items($db);
            $out[] = $row;
        }

        return $out;
    }

    public static function insert_new(mysqli $db, int $user_id, int $shipping_address_id): Order|false {
        $address = ShippingAddress::fetch_by_id($db, $shipping_address_id);
        if (!$address) return false;
        $stmt = $db->prepare("insert into `order` (user_id, first_name, last_name, phone_number, city, postal_code, building_number, apartment_number) value (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssss", $user_id, $address->first_name, $address->last_name, $address->phone_number, $address->city, $address->postal_code, $address->building_number, $address->apartment_number);
        $res = $stmt->execute();
        if (!$res) return false;
        return Order::fetch_by_id($db, $db->insert_id);
    }

    public static function insert_from_cart(mysqli $db, int $user_id, $shipping_address_id, bool $delete_cart = true): Order|false {
        $cart_items = CartItem::fetch_by_user_id($db, $user_id);
        if (!$cart_items) return false;

        $nOrder = Order::insert_new($db, $user_id, $shipping_address_id);
        if (!$nOrder) return false;

        foreach ($cart_items as $item) {
            $product = Product::fetch_by_id($db, $item->product_id);
            if (!$product) continue;
            $nOrder->append_item($db, $item->product_id, $product->price, $item->quantity);
            if ($delete_cart)
                $item->delete($db);
        }

        $nOrder->refresh($db);
        return $nOrder;
    }

}