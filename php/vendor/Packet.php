<?php

class Packet
{
    public ResponseCode $c;
    public mixed $d;

    public function __construct(ResponseCode $code, $body = null)
    {
        $this->c = $code;
        $this->d = $body;
    }

    public function json(): string {
        return json_encode(['c' => $this->c, 'd' => $this->d]);
    }
    public function __toString(): string
    {
        return $this->json();
    }
}

enum ResponseCode: int
{
   case SUCCESS = 0;
   case ERROR = 1;
}