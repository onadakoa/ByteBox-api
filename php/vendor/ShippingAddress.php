<?php

class ShippingAddress
{
    public int $id;
    public int $user_id;
    public string $first_name;
    public string $last_name;
    public string $street;
    public string $city;
    public string $postal_code;
    public string $building_number;
    public string|null $apartment_number;

    private mysqli $db;

    private function __construct(mysqli $db, int $id, int $user_id, string $first_name, string $last_name, string $street, string $city, string $postal_code, string $building_number, $apartment_number)
    {
        $this->db = $db;
        $this->id = $id;
        $this->user_id = $user_id;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->street = $street;
        $this->city = $city;
        $this->postal_code = $postal_code;
        $this->building_number = $building_number;
        $this->apartment_number = $apartment_number;
    }

    public static function fetch_by_id(mysqli $db, int $id): ShippingAddress|false
    {
        $query = "select * from shipping_address where shipping_address_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1) return false;
        $row = $result->fetch_assoc();
        return new ShippingAddress($db, $row['shipping_address_id'], $row['user_id'], $row['first_name'], $row['last_name'], $row['street'], $row['city'], $row['postal_code'], $row['building_number'], $row['apartment_number']);
    }

    public function fetch_author()
    {
        return User::user_by_id($this->db, $this->user_id);
    }
}