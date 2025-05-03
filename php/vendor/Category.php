<?php

class Category
{
    public int $id;
    public string $name;
    public array $alias = [];

    private function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
    public function __toString(): string
    {
        return json_encode($this);
    }

    public static function fetch_category(mysqli $db, int $id): Category | false
    {
        $query = "select * from category where category_id=$id";
        $result = $db->query($query);
        if ($result->num_rows < 1)
            return false;

        $row = $result->fetch_assoc();

        $out = new Category($id, $row["name"]);

        $query = "select * from category_alias where category_id=$id";
        $result = $db->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $out->alias[] = array("name" => $row["alias"], "alias_id" => $row["alias_id"]);
            }
        }
        return $out;
    }

    /**
     * @return Category[]|false
     */
    public static function fetch_all(mysqli $db) {
        $res = $db->query("select * from category");
        if (!$res) return false;

        $out = [];
        while ($row = $res->fetch_assoc()) {
            $obj = new Category($row["category_id"], $row["name"]);
            $AliasRes = $db->query("select * from category_alias where category_id={$row['category_id']}");
            if (!$AliasRes) continue;
            while ($alias = $AliasRes->fetch_assoc()) {
                $obj->alias[] = array("name" => $alias["alias"], "alias_id" => $alias["alias_id"]);
            }
            $out[] = $obj;
        }
        return $out;
    }
}