<?php

class User
{
    private int $user_id;
    private string $login;
    private string $password;
    public string $first_name;
    public string $last_name;
    private int $permission;
    private int $creation_date;
    private string $token;


    public function __construct(mysqli $db, int $ID) {
        $query = "SELECT *, UNIX_TIMESTAMP(creation_date) as creation_date FROM user WHERE user_id = $ID LIMIT 1";
        $res = $db->query($query);

        if ($res->num_rows == 0) {
            throw new Exception("User with id $ID not found in database");
        }
        $row = $res->fetch_object();

        $this->first_name = $row->first_name;
        $this->last_name = $row->last_name;
        $this->user_id = $row->user_id;
        $this->login = $row->login;
        $this->password = $row->password;
        $this->permission = $row->persmission;
        $this->creation_date = $row->creation_date;
        $this->token = $row->token;
    }

}