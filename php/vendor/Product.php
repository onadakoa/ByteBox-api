<?php

class Product
{
    public int $product_id;
    public int|null $attachment_id;
    public int $author_id;
    public int|null $category_id;

    public string $name;
    public string $description;
    public float $price;
    public int $stock;

    private mysqli $db;

    public function fill(mysqli $db, int $product_id, $attachment_id, int $author_id, int|null $category_id, string $name, string $description, float $price, int $stock)
    {
        $this->db=$db;
        $this->product_id = $product_id;
        $this->attachment_id = $attachment_id;
        $this->author_id = $author_id;
        $this->category_id = $category_id;
        $this->name = $name;
        $this->description = $description;
        $this->price = $price;
        $this->stock = $stock;
    }

    public static function fetch_by_id(mysqli $db, $id): Product|false {
        $query = "select * from product where product_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1) return false;
        $row = $result->fetch_assoc();

        $p = new Product();
        $p->fill($db, $row['product_id'], $row['attachment_id'], $row['author_id'], $row['category_id'], $row['name'], $row['description'], $row['price'], $row['stock']);
        return $p;
    }

    /**
     * @return Product[]|false
     */
    public static function fetch_all(mysqli $db, string $search = "", int $limit = 10, int $offset = 0, int $category_id = 0, int $price_in = 0, int $price_out = null, string $sort = "P_ASC"): array {
        $searchVal = "%{$search}%";
        $catQuery = "";
        if ($category_id > 0) $catQuery = "category_id=$category_id and";
        $priceQuery = "";
        if ($price_in > 0) $priceQuery = "price>$price_in and ";
        if ($price_out > $price_in) $priceQuery .= "price<$price_out and";

        $sortQuery = "";
        switch ($sort) {
            case "P_ASC":
                $sortQuery = "order by price asc";
                break;
            case "P_DESC":
                $sortQuery = "order by price desc";
                break;
        }

        $stmt = $db->prepare("select * from product where {$catQuery} {$priceQuery} (name like ? or product_id like ?) {$sortQuery} limit ? offset ?");
        $stmt->bind_param("ssii", $searchVal, $searchVal, $limit, $offset);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows < 1) return false;
        $out = [];
        while ($row = $res->fetch_object("Product")) {
            $out[] = $row;
        }

        return $out;
    }

    public function fetch_author(): User|false {
        return User::user_by_id($this->db, $this->author_id);
    }
    public function fetch_category(): Category|false {
        return Category::fetch_category($this->db, $this->category_id);
    }
    public function fetch_attachment(): Attachment|false {
        if ($this->attachment_id == null) return false;
        return Attachment::fetch_attachment($this->db, $this->attachment_id);
    }

}