<?php
class Attachment
{
    public int $attachment_id;
    public int $image_count;
    public int $author_id;
    public int $creation_date;
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
        $query = "select *, UNIX_TIMESTAMP(creation_date) as creation_date from attachment a left join image i on a.attachment_id=i.attachment_id where a.attachment_id=$id";
        $result = $db->query($query);
        if ($result->num_rows != 1) return false;
        $row = $result->fetch_assoc();
        $out = new Attachment($db, $id, $row['image_count'], $row['author_id'], $row['creation_date']);

        do {
            if ($row["path"] == null) continue;
            $out->images[] = new Image($row['image_id'], $id, $row['path'], $row['size']);
        } while($row = $result->fetch_assoc());

        return $out;
    }

    public function fetch_author(): User {
        return User::user_by_id($this->db, $this->author_id);
    }

}