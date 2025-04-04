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

        $query = "select alias, alias_id from category_alias where category_id=1";
        $result = $db->query($query);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $out->alias[] = array("name" => $row["alias"], "alias_id" => $row["alias_id"]);
            }
        }
        return $out;
    }
}