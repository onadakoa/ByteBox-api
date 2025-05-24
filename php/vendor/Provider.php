<?php

class Provider
{
    public int $provider_id;
    public string $name;

    public static function fetch_by_id(mysqli $db, $id): Provider|false {
        $res = $db->query("SELECT * FROM provider WHERE provider_id={$id}");
        if (!$res || $res->num_rows!=1) return false;
        return $res->fetch_object("Provider");
    }

    /**
     * @return Provider[]|false
     */
    public static function fetch_all(mysqli $db): array|false {
        $res = $db->query("select * from provider");
        if (!$res) return false;
        $out = [];
        while ($row = $res->fetch_object("Provider")) {
            $out[] = $row;
        }
        return $out;
    }

    public static function insert_new(mysqli $db, string $name): Provider|false {
        $stmt = $db->prepare("INSERT INTO provider (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if (!$stmt->execute()) return false;
        return Provider::fetch_by_id($db, $db->insert_id);
    }
}