<?php

class Image
{
    public int $id;
    private int $attachment_id;
    private string $path;
    public int $size;
    public string $type;

    public function __construct(int $id, int $attachment_id, string $path, int $size, string $type) {
        $this->id = $id;
        $this->attachment_id = $attachment_id;
        $this->path = $path;
        $this->size = $size;
        $this->type = $type;
    }

    public function getAttachmentId(): int
    {
        return $this->attachment_id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public static function fetch_image(mysqli $db, $id) {
        $res = $db->query("select * from image where image_id=$id");
        if ($res->num_rows != 1) {
            return false;
        }
        $row = $res->fetch_assoc();
        return new Image($id, $row['attachment_id'], $row['path'], $row['size'], $row['type']);
    }


}