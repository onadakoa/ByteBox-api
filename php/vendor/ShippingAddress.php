<?php

class ShippingAddress
{
    public int $shipping_address_id;
    public int $user_id;
    public string $first_name;
    public string $last_name;
    public string $street;
    public string $city;
    public string $postal_code;
    public string $building_number;
    public string $phone_number;
    public string|null $apartment_number;

    private function fill(int $id, int $user_id, string $first_name, string $last_name, string $street, string $city, string $postal_code, string $building_number, $apartment_number, string $phone_number)
    {
        $this->shipping_address_id = $id;
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->street = $street;
        $this->city = $city;
        $this->postal_code = $postal_code;
        $this->building_number = $building_number;
        $this->apartment_number = $apartment_number;
        $this->phone_number = $phone_number;
    }

    public static function fetch_by_id(mysqli $db, int $id): ShippingAddress|false
    {
        $query = "select * from shipping_address where shipping_address_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1) return false;
        return $result->fetch_object("ShippingAddress");
    }

    /**
     * @return ShippingAddress[]|false
     */
    public static function fetch_all(mysqli $db, $user_id) {
        $res = $db->query("select * from shipping_address where user_id={$user_id}");
        if (!$res) return false;

        $out = [];
        while ($row = $res->fetch_object("ShippingAddress")) {
            $out[] = $row;
        }

        return $out;
    }

    public function fetch_author(mysqli $db)
    {
        return User::user_by_id($db, $this->user_id);
    }
}