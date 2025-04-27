<?php

class Image
{
    public int $id;
    private int $attachment_id;
    private string $path;
    public int $size;

    public function __construct(int $id, int $attachment_id, string $path, int $size) {
        $this->id = $id;
        $this->attachment_id = $attachment_id;
        $this->path = $path;
        $this->size = $size;
    }

    public function getAttachmentId(): int
    {
        return $this->attachment_id;
    }

    public function getPath(): string
    {
        return $this->path;
    }


}