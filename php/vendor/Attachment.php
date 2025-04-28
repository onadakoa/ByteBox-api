<?php
class Attachment
{
    public int $attachment_id;
    public int $image_count;
    public int $author_id;
    public string $creation_date;
    public array $images = [];

    private mysqli $db;

    private function __construct(mysqli $db, $id, $image_count, $author_id, $creation_date)
    {
        $this->db = $db;
        $this->attachment_id = $id;
        $this->image_count = $image_count;
        $this->author_id = $author_id;
        $this->creation_date = $creation_date;
    }

    public static function fetch_attachment(mysqli $db, $id): Attachment | false {
        $query = "select *, UNIX_TIMESTAMP(creation_date) as creation_date from attachment where attachment_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1) return false;
        $row = $result->fetch_assoc();
        $out = new Attachment($db, $id, $row['image_count'], $row['author_id'], $row['creation_date']);

        $out->fill_images();

        return $out;
    }

    /**
     * @return Attachment[]|false
     */
    public static function fetch_all(mysqli $db, int $limit = 10, int $offset = 0): array|false {
        $query = "select *, unix_timestamp(creation_date) as creation_date from attachment limit $limit offset $offset";
        $res = $db->query($query);
        if ($res->num_rows < 1) return false;
        $out = [];

        while ($row = $res->fetch_assoc()) {
            $tmp = new Attachment($db, $row['attachment_id'], $row['image_count'], $row['author_id'], $row['creation_date']);
            $tmp->fill_images();

            $out[] = $tmp;
        }
        return $out;
    }

    public function fetch_author(): User {
        return User::user_by_id($this->db, $this->author_id);
    }
    public function fill_images(): void {
        $query = "select * from image where attachment_id={$this->attachment_id}";
        $res = $this->db->query($query);

        $this->images = [];
        while ($row = $res->fetch_assoc()) {
            $this->images[] = new Image($row['image_id'], $row['attachment_id'], $row['path'], $row['size'], $row['type']);
        }
    }

}