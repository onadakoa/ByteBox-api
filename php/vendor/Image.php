<?php

class Image
{
    public int $id;
    private int $attachment_id;
    private string|null $path;
    public int|null $size;
    public string|null $type;

    public function __construct(int $id, int $attachment_id, $path, $size, $type) {
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

    public function getPath(): string|null
    {
        return $this->path;
    }
    public function fill(int $newID=null): bool {
        $id = $newID ?? $this->id;
        $db = get_mysqli();

        $res = $db->query("select * from image where image_id=$id");
        if ($res->num_rows != 1) return false;
        $row = $res->fetch_assoc();

        $this->id = $row['image_id'];
        $this->attachment_id = $row['attachment_id'];
        $this->path = $row['path'];
        $this->size = $row['size'];
        $this->type = $row['type'];

        $db->close();
        return true;
    }

    public function exists(): bool {
        $db = get_mysqli();
        $res = $db->query("select * from image where image_id=" . $this->id);
        $db->close();
        if ($res->num_rows != 1) return false;
        return true;
    }

    public function update(string $path, string $type, int $size = -1): bool {
        if (!$this->exists()) return false;
        $db = get_mysqli();

        $sizeSTM = ($size>=0) ? ", size=$size" : "";
        $query = <<<sql
        update image set
                         path='$path',
                         type='$type'
                         $sizeSTM
        where image_id={$this->id}
        sql;

        $res = $db->query($query);
        if (!$res) return false;

        $db->close();
        $this->fill();
        return true;
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