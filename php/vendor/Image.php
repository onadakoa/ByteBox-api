<?php

class Image
{
    public int $id;
    public int $attachment_id;
    public string $path;
    public int $size;

    public function __construct(int $id, int $attachment_id, string $path, int $size) {
        $this->id = $id;
        $this->attachment_id = $attachment_id;
        $this->path = $path;
        $this->size = $size;
    }


}