<?php

class User
{
    public int $user_id;
    public string $login;
    private string $password;
    public string $first_name;
    public string $last_name;
    public int $permission;
    public int $creation_date;
    private string $token;

    private function setup($row) {
        $this->first_name = $row->first_name;
        $this->last_name = $row->last_name;
        $this->user_id = $row->user_id;
        $this->login = $row->login;
        $this->password = $row->password;
        $this->permission = $row->persmission;
        $this->creation_date = $row->creation_date;
        $this->token = $row->token;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public static function user_by_id(mysqli $db, int $id) {
        $query = "SELECT *, UNIX_TIMESTAMP(creation_date) as creation_date FROM user WHERE user_id = $id LIMIT 1";
        $res = $db->query($query);

        if ($res->num_rows == 0) {
            return false;
        }
        $row = $res->fetch_object();

        $u = new User();
        $u->setup($row);
        return $u;
    }
    public static function user_by_token(mysqli $db, $token) {
        $query = "SELECT *, UNIX_TIMESTAMP(creation_date) as creation_date FROM user WHERE token = '$token' LIMIT 1";
        $res = $db->query($query);

        if ($res->num_rows == 0) {
            return false;
        }
        $row = $res->fetch_object();

        $u = new User();
        $u->setup($row);

        return $u;
    }
    public static function user_by_credentials(mysqli $db, string $login, string $password) {
        $query = "select *, UNIX_TIMESTAMP(creation_date) as creation_date from user where login = '$login'";
        $res = $db->query($query);

        if ($res->num_rows != 1) {
            return false;
        }
        $row = $res->fetch_object();

        if (!password_verify($password, $row->password))
            return false;

        $u = new User();
        $u->setup($row);

        return $u;
    }

}